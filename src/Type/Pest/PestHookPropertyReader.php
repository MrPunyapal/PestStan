<?php

declare(strict_types=1);

namespace PestStan\Type\Pest;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;

/**
 * Parses test files and Pest.php config files to extract dynamic property types from beforeEach/beforeAll hooks.
 */
final class PestHookPropertyReader
{
    private const HOOK_FUNCTIONS = ['beforeEach', 'beforeAll'];

    /** @var array<string, array<string, Type>> Maps normalized file paths to property name → Type */
    private array $filePropertyCache = [];

    /** @var array<string, array<string, Type>> Maps normalized directory paths to property name → Type */
    private array $directoryPropertyMap = [];

    private bool $pestFilesParsed = false;

    /**
     * @param  string[]  $scanPaths  PHPStan's configured analysis paths
     */
    public function __construct(
        private readonly array $scanPaths,
    ) {}

    /**
     * Returns property types resolved from hook closures for the given file.
     *
     * @return array<string, Type>
     */
    public function getPropertyTypes(string $filePath): array
    {
        $this->ensurePestFilesParsed();

        $normalizedFile = $this->normalizePath($filePath);

        if (! isset($this->filePropertyCache[$normalizedFile])) {
            $this->filePropertyCache[$normalizedFile] = $this->parseTestFile($filePath);
        }

        $properties = $this->filePropertyCache[$normalizedFile];

        $directoryProperties = $this->resolveDirectoryProperties($filePath);
        foreach ($directoryProperties as $name => $type) {
            $properties[$name] = isset($properties[$name]) ? TypeCombinator::union($properties[$name], $type) : $type;
        }

        return $properties;
    }

    /**
     * Resolves directory-scoped properties from Pest.php beforeEach hooks.
     *
     * @return array<string, Type>
     */
    private function resolveDirectoryProperties(string $filePath): array
    {
        $normalizedFile = $this->normalizePath($filePath);

        $bestMatch = null;
        $bestLength = 0;

        foreach ($this->directoryPropertyMap as $directory => $properties) {
            if (! str_starts_with($normalizedFile, $directory)) {
                continue;
            }

            $dirLength = strlen($directory);
            if ($dirLength > $bestLength) {
                $bestMatch = $properties;
                $bestLength = $dirLength;
            }
        }

        return $bestMatch ?? [];
    }

    private function ensurePestFilesParsed(): void
    {
        if ($this->pestFilesParsed) {
            return;
        }

        $this->pestFilesParsed = true;

        $pestFiles = $this->discoverPestFiles();

        foreach ($pestFiles as $pestFile) {
            $this->parsePestConfigFile($pestFile);
        }
    }

    /**
     * Discovers Pest.php files from scan paths.
     *
     * @return string[]
     */
    private function discoverPestFiles(): array
    {
        $files = [];

        foreach ($this->scanPaths as $scanPath) {
            $realPath = realpath($scanPath);
            if ($realPath === false) {
                continue;
            }

            $dir = is_file($realPath) ? dirname($realPath) : $realPath;
            $this->findPestFilesInDirectory($dir, $files);
        }

        return array_unique($files);
    }

    /**
     * Recursively finds Pest.php files in a directory.
     *
     * @param  string[]  $results
     */
    private function findPestFilesInDirectory(string $directory, array &$results): void
    {
        $pestFile = $directory . DIRECTORY_SEPARATOR . 'Pest.php';
        if (is_file($pestFile)) {
            $realPath = realpath($pestFile);
            if ($realPath !== false) {
                $results[] = $realPath;
            }
        }

        $entries = scandir($directory);
        if ($entries === false) {
            return;
        }

        foreach ($entries as $entry) {
            if ($entry === '.') {
                continue;
            }
            if ($entry === '..') {
                continue;
            }
            $path = $directory . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($path)) {
                $this->findPestFilesInDirectory($path, $results);
            }
        }
    }

    /**
     * Parses a Pest.php config file to extract beforeEach property assignments from uses() chains.
     */
    private function parsePestConfigFile(string $filePath): void
    {
        $stmts = $this->parseFile($filePath);
        if ($stmts === null) {
            return;
        }

        $nodeFinder = new NodeFinder;

        $this->extractUsesBeforeEachProperties($nodeFinder, $stmts);
    }

    /**
     * Extracts property types from uses()->beforeEach(Closure)->in() chains.
     *
     * @param  Node[]  $stmts
     */
    private function extractUsesBeforeEachProperties(NodeFinder $nodeFinder, array $stmts): void
    {
        $methodCalls = $nodeFinder->findInstanceOf($stmts, MethodCall::class);

        foreach ($methodCalls as $methodCall) {
            if (! $this->isMethodNamed($methodCall, 'beforeEach')) {
                continue;
            }

            if (! $this->isUsesChain($methodCall->var)) {
                continue;
            }

            $closure = $this->extractClosureArg($methodCall);
        }
    }

    /**
     * Checks if the expression is part of a uses() call chain.
     */
    private function isUsesChain(Expr $expr): bool
    {
        if ($expr instanceof FuncCall) {
            return $expr->name instanceof Name && $expr->name->toString() === 'uses';
        }

        if ($expr instanceof MethodCall) {
            return $this->isUsesChain($expr->var);
        }

        return false;
    }

    /**
     * Checks if a method call chain contains a specific node.
     */
    private function chainContainsNode(Expr $expr, MethodCall $target): bool
    {
        if ($expr === $target) {
            return true;
        }

        if ($expr instanceof MethodCall) {
            return $this->chainContainsNode($expr->var, $target);
        }

        return false;
    }

    /**
     * Parses a test file to extract property types from hook closures.
     *
     * @return array<string, Type>
     */
    private function parseTestFile(string $filePath): array
    {
        $stmts = $this->parseFile($filePath);
        if ($stmts === null) {
            return [];
        }

        $nodeFinder = new NodeFinder;
        $properties = [];

        $funcCalls = $nodeFinder->findInstanceOf($stmts, FuncCall::class);

        foreach ($funcCalls as $funcCall) {
            if (! $funcCall->name instanceof Name) {
                continue;
            }

            $functionName = $funcCall->name->toString();
            if (! in_array($functionName, self::HOOK_FUNCTIONS, true)) {
                continue;
            }

            $closure = $this->extractClosureArg($funcCall);
        }

        return $properties;
    }

    /**
     * Extracts the Closure argument from a function/method call.
     */
    private function extractClosureArg(FuncCall|MethodCall $call): ?Closure
    {
        foreach ($call->getArgs() as $arg) {
            if ($arg->value instanceof Closure) {
                return $arg->value;
            }
        }

        return null;
    }

    /**
     * Parses a PHP file into AST with name resolution.
     *
     * @return Node[]|null
     */
    private function parseFile(string $filePath): ?array
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return null;
        }

        $parser = (new ParserFactory)->createForNewestSupportedVersion();
        $stmts = $parser->parse($content);

        if ($stmts === null) {
            return null;
        }

        $traverser = new NodeTraverser;
        $traverser->addVisitor(new NameResolver);

        return $traverser->traverse($stmts);
    }

    private function isMethodNamed(MethodCall $methodCall, string $name): bool
    {
        return $methodCall->name instanceof Identifier && $methodCall->name->toString() === $name;
    }

    private function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }
}
