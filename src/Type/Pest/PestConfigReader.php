<?php

declare(strict_types=1);

namespace PestStan\Type\Pest;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeFinder;

/**
 * Parses Pest.php config files to resolve which TestCase class is bound via uses(), pest()->extend(), or pest()->use().
 */
final class PestConfigReader
{
    /** @var array<string, list<string>> Maps normalized directory paths to fully-qualified class/trait names */
    private array $directoryMap = [];

    /** @var array<string, list<array{class: string, config: string}>> */
    private array $globalUseDirectoryMap = [];

    private bool $parsed = false;

    /**
     * @param  string[]  $pestConfigFiles  Explicit Pest.php file paths from config
     */
    public function __construct(
        private readonly array $pestConfigFiles,
        private readonly PestFileDiscoverer $fileDiscoverer,
    ) {}

    /**
     * Resolves all classes and traits bound to the given file via uses(), pest()->extend(), or pest()->use().
     *
     * @return list<string>
     */
    public function resolveBindings(string $filePath): array
    {
        $this->ensureParsed();

        $normalizedFile = $this->fileDiscoverer->normalizePath($filePath);
        $bindings = [];

        foreach ($this->directoryMap as $directory => $classNames) {
            if (! str_starts_with($normalizedFile, $directory)) {
                continue;
            }

            array_push($bindings, ...$classNames);
        }

        return array_values(array_unique($bindings));
    }

    /**
     * Resolves statically known pest()->use(...)->in(...) declarations that cover the given file.
     *
     * @return list<array{class: string, config: string}>
     */
    public function resolveGlobalUses(string $filePath): array
    {
        $this->ensureParsed();

        $normalizedFile = $this->fileDiscoverer->normalizePath($filePath);
        $bindings = [];

        foreach ($this->globalUseDirectoryMap as $directory => $directoryBindings) {
            if (str_starts_with($normalizedFile, $directory)) {
                array_push($bindings, ...$directoryBindings);
            }
        }

        return $bindings;
    }

    private function ensureParsed(): void
    {
        if ($this->parsed) {
            return;
        }

        $this->parsed = true;

        $pestFiles = $this->fileDiscoverer->discoverPestFiles($this->pestConfigFiles);

        foreach ($pestFiles as $pestFile) {
            $this->parsePestFile($pestFile);
        }
    }

    private function parsePestFile(string $filePath): void
    {
        $parsed = $this->fileDiscoverer->parseFile($filePath);
        if ($parsed === null) {
            return;
        }

        [$stmts] = $parsed;

        $nodeFinder = new NodeFinder;
        $pestFileDir = dirname($filePath);

        $this->extractUsesBindings($nodeFinder, $stmts, $pestFileDir);
        $this->extractPestBindings($nodeFinder, $stmts, $pestFileDir, $filePath);
    }

    /**
     * Extracts uses(TestCase::class, Trait::class)->in('Feature', 'Unit') bindings.
     *
     * @param  Node[]  $stmts
     */
    private function extractUsesBindings(NodeFinder $nodeFinder, array $stmts, string $pestFileDir): void
    {
        $methodCalls = $nodeFinder->findInstanceOf($stmts, MethodCall::class);

        foreach ($methodCalls as $methodCall) {
            if (! $this->fileDiscoverer->isMethodNamed($methodCall, 'in')) {
                continue;
            }

            $classNames = $this->resolveUsesClassNames($methodCall->var);
            if ($classNames === []) {
                continue;
            }

            $this->registerDirectoryBindings($classNames, $methodCall, $pestFileDir);
        }

        $funcCalls = $nodeFinder->findInstanceOf($stmts, FuncCall::class);
        foreach ($funcCalls as $funcCall) {
            if (! $this->isFuncNamed($funcCall, 'uses')) {
                continue;
            }

            if ($this->hasInChain($funcCall, $stmts, $nodeFinder)) {
                continue;
            }

            $classNames = $this->extractAllClassArgs($funcCall);
            if ($classNames !== []) {
                $this->appendBindings($this->fileDiscoverer->normalizePath($pestFileDir) . '/', $classNames);
            }
        }
    }

    /**
     * Extracts pest()->extend(TestCase::class)->in(...) and pest()->use(Trait::class)->in(...) bindings.
     *
     * @param  Node[]  $stmts
     */
    private function extractPestBindings(NodeFinder $nodeFinder, array $stmts, string $pestFileDir, string $pestFile): void
    {
        $methodCalls = $nodeFinder->findInstanceOf($stmts, MethodCall::class);

        foreach ($methodCalls as $methodCall) {
            if (! $this->fileDiscoverer->isMethodNamed($methodCall, 'in')) {
                continue;
            }

            $pestBindings = $this->resolvePestClassNames($methodCall->var);
            if ($pestBindings === null) {
                continue;
            }

            $classNames = [...$pestBindings['extend'], ...$pestBindings['use']];
            if ($classNames !== []) {
                $this->registerDirectoryBindings($classNames, $methodCall, $pestFileDir);
            }

            if ($pestBindings['use'] !== []) {
                $this->registerGlobalUseBindings($pestBindings['use'], $methodCall, $pestFileDir, $pestFile);
            }
        }

        foreach ($methodCalls as $methodCall) {
            if (! $this->isPestExtendOrUseMethod($methodCall)) {
                continue;
            }

            if (! $this->isPestFuncCall($methodCall->var)) {
                continue;
            }

            if ($this->isPartOfInChain($methodCall, $stmts, $nodeFinder)) {
                continue;
            }

            $classNames = $this->extractAllClassArgs($methodCall);
            if ($classNames !== []) {
                $this->appendBindings($this->fileDiscoverer->normalizePath($pestFileDir) . '/', $classNames);
            }
        }
    }

    /**
     * Walks up uses(...)->...->in() chain to find all classes/traits from uses() call.
     *
     * @return list<string>
     */
    private function resolveUsesClassNames(Expr $expr): array
    {
        if ($expr instanceof FuncCall && $this->isFuncNamed($expr, 'uses')) {
            return $this->extractAllClassArgs($expr);
        }

        if ($expr instanceof MethodCall) {
            return $this->resolveUsesClassNames($expr->var);
        }

        return [];
    }

    /**
     * Walks a chain rooted at pest() and keeps extend() and use() classes separate.
     *
     * @return array{extend: list<string>, use: list<string>}|null
     */
    private function resolvePestClassNames(Expr $expr): ?array
    {
        if ($this->isPestFuncCall($expr)) {
            return ['extend' => [], 'use' => []];
        }

        if (! $expr instanceof MethodCall) {
            return null;
        }

        $bindings = $this->resolvePestClassNames($expr->var);
        if ($bindings === null) {
            return null;
        }

        if ($this->fileDiscoverer->isMethodNamed($expr, 'extend')) {
            array_push($bindings['extend'], ...$this->extractAllClassArgs($expr));
        } elseif ($this->fileDiscoverer->isMethodNamed($expr, 'use')) {
            array_push($bindings['use'], ...$this->extractAllClassArgs($expr));
        }

        return $bindings;
    }

    private function isPestExtendOrUseMethod(MethodCall $methodCall): bool
    {
        if ($this->fileDiscoverer->isMethodNamed($methodCall, 'extend')) {
            return true;
        }

        return $this->fileDiscoverer->isMethodNamed($methodCall, 'use');
    }

    private function isPestFuncCall(Expr $expr): bool
    {
        return $expr instanceof FuncCall && $this->isFuncNamed($expr, 'pest');
    }

    /**
     * Extracts directory strings from ->in('Feature', 'Unit') arguments.
     *
     * Returns null when any path is dynamic, because coverage cannot be proven.
     *
     * @return list<string>|null
     */
    private function extractInDirectories(MethodCall $methodCall): ?array
    {
        $directories = [];

        foreach ($methodCall->getArgs() as $arg) {
            $value = $arg->value;

            if ($value instanceof String_) {
                $directories[] = $value->value;

                continue;
            }

            if (! $value instanceof ConstFetch) {
                return null;
            }

            $name = $value->name->toString();
            if ($name === '__DIR__') {
                $directories[] = '.';

                continue;
            }

            return null;
        }

        return $directories;
    }

    /**
     * Maps class/trait names to directories from an ->in() method call.
     *
     * @param  list<string>  $classNames
     */
    private function registerDirectoryBindings(array $classNames, MethodCall $inMethodCall, string $pestFileDir): void
    {
        $directories = $this->extractInDirectories($inMethodCall);

        if ($directories === null || $directories === []) {
            return;
        }

        foreach ($directories as $dir) {
            $fullPath = $this->fileDiscoverer->normalizePath($pestFileDir . '/' . $dir) . '/';
            $this->appendBindings($fullPath, $classNames);
        }
    }

    /**
     * @param  list<string>  $classNames
     */
    private function registerGlobalUseBindings(
        array $classNames,
        MethodCall $inMethodCall,
        string $pestFileDir,
        string $pestFile,
    ): void {
        $directories = $this->extractInDirectories($inMethodCall);
        if ($directories === null || $directories === []) {
            return;
        }

        foreach ($directories as $dir) {
            $directory = $this->fileDiscoverer->normalizePath($pestFileDir . '/' . $dir) . '/';
            $this->globalUseDirectoryMap[$directory] ??= [];

            foreach ($classNames as $className) {
                $this->globalUseDirectoryMap[$directory][] = [
                    'class' => $className,
                    'config' => $this->fileDiscoverer->normalizePath($pestFile),
                ];
            }
        }
    }

    /**
     * Appends class/trait names to the directory map.
     *
     * @param  list<string>  $classNames
     */
    private function appendBindings(string $directory, array $classNames): void
    {
        if (! isset($this->directoryMap[$directory])) {
            $this->directoryMap[$directory] = [];
        }

        array_push($this->directoryMap[$directory], ...$classNames);
    }

    /**
     * Extracts all class/trait names from function/method call arguments.
     *
     * @return list<string>
     */
    private function extractAllClassArgs(FuncCall|MethodCall $call): array
    {
        $classNames = [];

        foreach ($call->getArgs() as $arg) {
            $value = $arg->value;

            if ($value instanceof ClassConstFetch && $value->name instanceof Identifier && $value->name->toString() === 'class' && $value->class instanceof Name) {
                $classNames[] = $value->class->toString();

                continue;
            }

            if ($value instanceof String_) {
                $classNames[] = $value->value;
            }
        }

        return $classNames;
    }

    private function isFuncNamed(FuncCall $funcCall, string $name): bool
    {
        return $funcCall->name instanceof Name && $funcCall->name->toString() === $name;
    }

    /**
     * Checks whether a uses() FuncCall is part of a chain that ends with ->in().
     *
     * @param  Node[]  $stmts
     */
    private function hasInChain(FuncCall $funcCall, array $stmts, NodeFinder $nodeFinder): bool
    {
        $methodCalls = $nodeFinder->findInstanceOf($stmts, MethodCall::class);

        foreach ($methodCalls as $methodCall) {
            if (! $this->fileDiscoverer->isMethodNamed($methodCall, 'in')) {
                continue;
            }

            if ($this->resolveUsesClassNames($methodCall->var) !== [] && $this->chainContainsNode($methodCall->var, $funcCall)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether a pest()->extend() or pest()->use() MethodCall is part of a chain with ->in().
     *
     * @param  Node[]  $stmts
     */
    private function isPartOfInChain(MethodCall $extendCall, array $stmts, NodeFinder $nodeFinder): bool
    {
        $methodCalls = $nodeFinder->findInstanceOf($stmts, MethodCall::class);

        foreach ($methodCalls as $methodCall) {
            if (! $this->fileDiscoverer->isMethodNamed($methodCall, 'in')) {
                continue;
            }

            if ($this->chainContainsNode($methodCall->var, $extendCall)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Walks the call chain to check if a target node is present (by identity).
     */
    private function chainContainsNode(Expr $expr, Expr $target): bool
    {
        if ($expr === $target) {
            return true;
        }

        if ($expr instanceof MethodCall) {
            return $this->chainContainsNode($expr->var, $target);
        }

        return false;
    }
}
