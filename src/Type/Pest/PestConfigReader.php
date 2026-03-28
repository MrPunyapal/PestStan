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
 * Parses Pest.php config files to resolve which TestCase class is bound via uses() or pest()->extend().
 */
final class PestConfigReader
{
    /** @var array<string, string> Maps normalized directory paths to fully-qualified TestCase class names */
    private array $directoryMap = [];

    private bool $parsed = false;

    /**
     * @param  string[]  $pestConfigFiles  Explicit Pest.php file paths from config
     */
    public function __construct(
        private readonly array $pestConfigFiles,
        private readonly PestFileDiscoverer $fileDiscoverer,
    ) {}

    /**
     * Resolves the TestCase class for a given file being analyzed.
     */
    public function resolveTestCaseClass(string $filePath): ?string
    {
        $this->ensureParsed();

        $normalizedFile = $this->fileDiscoverer->normalizePath($filePath);

        $bestMatch = null;
        $bestLength = 0;

        foreach ($this->directoryMap as $directory => $className) {
            if (! str_starts_with($normalizedFile, $directory)) {
                continue;
            }

            $dirLength = strlen($directory);
            if ($dirLength > $bestLength) {
                $bestMatch = $className;
                $bestLength = $dirLength;
            }
        }

        return $bestMatch;
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
        $this->extractPestExtendBindings($nodeFinder, $stmts, $pestFileDir);
    }

    /**
     * Extracts uses(TestCase::class)->in('Feature', 'Unit') bindings.
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

            $className = $this->resolveUsesClassName($methodCall->var);
            if ($className === null) {
                continue;
            }

            $directories = $this->extractInDirectories($methodCall);
            if ($directories === []) {
                $this->directoryMap[$this->fileDiscoverer->normalizePath($pestFileDir) . '/'] = $className;

                continue;
            }

            foreach ($directories as $dir) {
                $fullPath = $this->fileDiscoverer->normalizePath($pestFileDir . '/' . $dir) . '/';
                $this->directoryMap[$fullPath] = $className;
            }
        }

        $funcCalls = $nodeFinder->findInstanceOf($stmts, FuncCall::class);
        foreach ($funcCalls as $funcCall) {
            if (! $this->isFuncNamed($funcCall, 'uses')) {
                continue;
            }

            if ($this->hasInChain($funcCall, $stmts, $nodeFinder)) {
                continue;
            }

            $className = $this->extractFirstClassArg($funcCall);
            if ($className !== null) {
                $this->directoryMap[$this->fileDiscoverer->normalizePath($pestFileDir) . '/'] = $className;
            }
        }
    }

    /**
     * Extracts pest()->extend(TestCase::class)->in(...) bindings.
     *
     * @param  Node[]  $stmts
     */
    private function extractPestExtendBindings(NodeFinder $nodeFinder, array $stmts, string $pestFileDir): void
    {
        $methodCalls = $nodeFinder->findInstanceOf($stmts, MethodCall::class);

        foreach ($methodCalls as $methodCall) {
            if (! $this->fileDiscoverer->isMethodNamed($methodCall, 'in')) {
                continue;
            }

            $className = $this->resolvePestExtendClassName($methodCall->var);
            if ($className === null) {
                continue;
            }

            $directories = $this->extractInDirectories($methodCall);
            if ($directories === []) {
                $this->directoryMap[$this->fileDiscoverer->normalizePath($pestFileDir) . '/'] = $className;

                continue;
            }

            foreach ($directories as $dir) {
                $fullPath = $this->fileDiscoverer->normalizePath($pestFileDir . '/' . $dir) . '/';
                $this->directoryMap[$fullPath] = $className;
            }
        }

        foreach ($methodCalls as $methodCall) {
            if (! $this->fileDiscoverer->isMethodNamed($methodCall, 'extend')) {
                continue;
            }

            if (! $this->isPestFuncCall($methodCall->var)) {
                continue;
            }

            if ($this->isPartOfInChain($methodCall, $stmts, $nodeFinder)) {
                continue;
            }

            $className = $this->extractFirstClassArg($methodCall);
            if ($className !== null) {
                $this->directoryMap[$this->fileDiscoverer->normalizePath($pestFileDir) . '/'] = $className;
            }
        }
    }

    /**
     * Walks up uses(...)->...->in() chain to find the class from uses() call.
     */
    private function resolveUsesClassName(Expr $expr): ?string
    {
        // Direct: uses(X::class)->in(...)
        if ($expr instanceof FuncCall && $this->isFuncNamed($expr, 'uses')) {
            return $this->extractFirstClassArg($expr);
        }

        // Chained: uses(X::class)->something()->in(...)
        if ($expr instanceof MethodCall) {
            return $this->resolveUsesClassName($expr->var);
        }

        return null;
    }

    /**
     * Walks up pest()->extend(X::class)->...->in() chain.
     */
    private function resolvePestExtendClassName(Expr $expr): ?string
    {
        // Direct: pest()->extend(X::class)->in(...)
        if ($expr instanceof MethodCall && $this->fileDiscoverer->isMethodNamed($expr, 'extend') && $this->isPestFuncCall($expr->var)) {
            return $this->extractFirstClassArg($expr);
        }

        // Chained: pest()->extend(X::class)->something()->in(...)
        if ($expr instanceof MethodCall) {
            return $this->resolvePestExtendClassName($expr->var);
        }

        return null;
    }

    private function isPestFuncCall(Expr $expr): bool
    {
        return $expr instanceof FuncCall && $this->isFuncNamed($expr, 'pest');
    }

    /**
     * Extracts directory strings from ->in('Feature', 'Unit') arguments.
     *
     * @return string[]
     */
    private function extractInDirectories(MethodCall $methodCall): array
    {
        $directories = [];

        foreach ($methodCall->getArgs() as $arg) {
            if ($arg->value instanceof String_) {
                $directories[] = $arg->value->value;
            } elseif ($arg->value instanceof ConstFetch) {
                $name = $arg->value->name->toString();
                if ($name === '__DIR__') {
                    $directories[] = '.';
                }
            }
        }

        return $directories;
    }

    /**
     * Extracts the class name from the first argument of a function/method call (e.g., uses(TestCase::class)).
     */
    private function extractFirstClassArg(FuncCall|MethodCall $call): ?string
    {
        $args = $call->getArgs();
        if ($args === []) {
            return null;
        }

        $firstArg = $args[0]->value;

        if ($firstArg instanceof ClassConstFetch && $firstArg->name instanceof Identifier && $firstArg->name->toString() === 'class' && $firstArg->class instanceof Name) {
            return $firstArg->class->toString();
        }

        if ($firstArg instanceof String_) {
            return $firstArg->value;
        }

        return null;
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

            if ($this->resolveUsesClassName($methodCall->var) !== null && $this->chainContainsNode($methodCall->var, $funcCall)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether a pest()->extend() MethodCall is part of a chain with ->in().
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
