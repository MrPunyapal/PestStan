<?php

declare(strict_types=1);

namespace PestStan\Type\Pest;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

/**
 * Parses test files and Pest.php config files to extract dynamic property expressions from beforeEach/beforeAll hooks.
 */
final class PestHookPropertyReader
{
    private const HOOK_FUNCTIONS = ['beforeEach', 'beforeAll'];

    /** @var array<string, array<string, list<Expr>>> Maps normalized file paths to property name → list of RHS Exprs */
    private array $filePropertyCache = [];

    /** @var array<string, array<string, list<Expr>>> Maps normalized directory paths to property name → list of RHS Exprs */
    private array $directoryPropertyMap = [];

    private bool $pestFilesParsed = false;

    /**
     * @param  string[]  $scanPaths  PHPStan's configured analysis paths
     */
    public function __construct(
        private readonly array $scanPaths,
    ) {}

    /**
     * Returns property expressions from hook closures for the given file, to be resolved via Scope::getType().
     *
     * @return array<string, list<Expr>>
     */
    public function getPropertyExprs(string $filePath): array
    {
        $this->ensurePestFilesParsed();

        $normalizedFile = $this->normalizePath($filePath);

        if (! isset($this->filePropertyCache[$normalizedFile])) {
            $this->filePropertyCache[$normalizedFile] = $this->parseTestFile($filePath);
        }

        $properties = $this->filePropertyCache[$normalizedFile];

        $directoryProperties = $this->resolveDirectoryProperties($filePath);
        foreach ($directoryProperties as $name => $exprs) {
            if (isset($properties[$name])) {
                $properties[$name] = array_merge($properties[$name], $exprs);
            } else {
                $properties[$name] = $exprs;
            }
        }

        return $properties;
    }

    /**
     * Resolves directory-scoped properties from Pest.php beforeEach hooks.
     *
     * @return array<string, list<Expr>>
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
        $parsed = $this->parseFile($filePath);
        if ($parsed === null) {
            return;
        }

        [$stmts, $useMap] = $parsed;

        $nodeFinder = new NodeFinder;
        $pestFileDir = dirname($filePath);

        $this->extractUsesBeforeEachProperties($nodeFinder, $stmts, $pestFileDir, $useMap);
    }

    /**
     * Extracts property expressions from uses()->beforeEach(Closure)->in() chains.
     *
     * @param  Node[]  $stmts
     * @param  array<string, string>  $useMap
     */
    private function extractUsesBeforeEachProperties(NodeFinder $nodeFinder, array $stmts, string $pestFileDir, array $useMap): void
    {
        /** @var MethodCall[] $methodCalls */
        $methodCalls = $nodeFinder->findInstanceOf($stmts, MethodCall::class);

        foreach ($methodCalls as $methodCall) {
            if (! $this->isMethodNamed($methodCall, 'beforeEach')) {
                continue;
            }

            if (! $this->isUsesChain($methodCall->var)) {
                continue;
            }

            $closure = $this->extractClosureArg($methodCall);
            if ($closure === null) {
                continue;
            }

            $properties = $this->extractPropertyAssignments($closure, $useMap);
            if ($properties === []) {
                continue;
            }

            $directories = $this->resolveInDirectories($methodCall, $stmts, $nodeFinder);

            if ($directories === []) {
                $normalizedDir = $this->normalizePath($pestFileDir) . '/';
                $this->mergeDirectoryProperties($normalizedDir, $properties);
            } else {
                foreach ($directories as $dir) {
                    $fullPath = $this->normalizePath($pestFileDir . '/' . $dir) . '/';
                    $this->mergeDirectoryProperties($fullPath, $properties);
                }
            }
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
     * Resolves ->in() directories by looking up the chain from a beforeEach call.
     *
     * @param  Node[]  $stmts
     * @return string[]
     */
    private function resolveInDirectories(MethodCall $beforeEachCall, array $stmts, NodeFinder $nodeFinder): array
    {
        /** @var MethodCall[] $allMethodCalls */
        $allMethodCalls = $nodeFinder->findInstanceOf($stmts, MethodCall::class);

        foreach ($allMethodCalls as $methodCall) {
            if (! $this->isMethodNamed($methodCall, 'in')) {
                continue;
            }

            if ($this->chainContainsNode($methodCall->var, $beforeEachCall)) {
                return $this->extractStringArgs($methodCall);
            }
        }

        return [];
    }

    /**
     * Merges property expressions into the directory property map.
     *
     * @param  array<string, list<Expr>>  $properties
     */
    private function mergeDirectoryProperties(string $directory, array $properties): void
    {
        foreach ($properties as $name => $exprs) {
            if (isset($this->directoryPropertyMap[$directory][$name])) {
                $this->directoryPropertyMap[$directory][$name] = array_merge(
                    $this->directoryPropertyMap[$directory][$name],
                    $exprs,
                );
            } else {
                $this->directoryPropertyMap[$directory][$name] = $exprs;
            }
        }
    }

    /**
     * Parses a test file to extract property expressions from hook closures.
     *
     * @return array<string, list<Expr>>
     */
    private function parseTestFile(string $filePath): array
    {
        $parsed = $this->parseFile($filePath);
        if ($parsed === null) {
            return [];
        }

        [$stmts, $useMap] = $parsed;

        $nodeFinder = new NodeFinder;
        $properties = [];

        /** @var FuncCall[] $funcCalls */
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
            if ($closure === null) {
                continue;
            }

            $hookProperties = $this->extractPropertyAssignments($closure, $useMap);
            foreach ($hookProperties as $name => $exprs) {
                if (isset($properties[$name])) {
                    $properties[$name] = array_merge($properties[$name], $exprs);
                } else {
                    $properties[$name] = $exprs;
                }
            }
        }

        return $properties;
    }

    /**
     * Extracts $this->property = value assignments from a closure body, storing the RHS Expr for later scope-aware resolution.
     *
     * @param  array<string, string>  $useMap
     * @return array<string, list<Expr>>
     */
    private function extractPropertyAssignments(Closure $closure, array $useMap): array
    {
        $properties = [];
        $localVarExprs = $this->buildLocalVarExprMap($closure, $useMap);

        foreach ($closure->stmts ?? [] as $stmt) {
            if (! $stmt instanceof Expression) {
                continue;
            }

            if (! $stmt->expr instanceof Assign) {
                continue;
            }

            /** @var Assign $assign */
            $assign = $stmt->expr;

            if (! $assign->var instanceof PropertyFetch) {
                continue;
            }

            /** @var PropertyFetch $propertyFetch */
            $propertyFetch = $assign->var;

            if (! $propertyFetch->var instanceof Variable || $propertyFetch->var->name !== 'this') {
                continue;
            }

            if (! $propertyFetch->name instanceof Identifier) {
                continue;
            }

            $propertyName = $propertyFetch->name->name;

            $resolvedExpr = $this->resolveExprFromDocComment($stmt->getDocComment(), $propertyName, $useMap)
                ?? $this->resolveLocalVarExpr($assign->expr, $localVarExprs)
                ?? $assign->expr;

            $properties[$propertyName][] = $resolvedExpr;
        }

        return $properties;
    }

    /**
     * Builds a map of local variable names to their RHS Expr, with @var annotation overrides for synthetic type hints.
     *
     * @param  array<string, string>  $useMap
     * @return array<string, Expr>
     */
    private function buildLocalVarExprMap(Closure $closure, array $useMap): array
    {
        $localVarExprs = [];

        foreach ($closure->stmts ?? [] as $stmt) {
            if (! $stmt instanceof Expression) {
                continue;
            }

            if (! $stmt->expr instanceof Assign) {
                continue;
            }

            /** @var Assign $assign */
            $assign = $stmt->expr;

            if (! $assign->var instanceof Variable) {
                continue;
            }

            $varName = $assign->var->name;
            if (! is_string($varName)) {
                continue;
            }

            $docComment = $stmt->getDocComment();

            if ($docComment !== null && preg_match('/@var\s+([\w\\\\]+)\s+\$(\w+)/', $docComment->getText(), $matches) && $matches[2] === $varName) {
                $syntheticExpr = $this->buildSyntheticExprFromTypeName($matches[1], $useMap);
                if ($syntheticExpr !== null) {
                    $localVarExprs[$varName] = $syntheticExpr;
                    continue;
                }
            }

            $localVarExprs[$varName] = $assign->expr;
        }

        return $localVarExprs;
    }

    /**
     * Resolves a local variable reference to its stored RHS Expr.
     *
     * @param  array<string, Expr>  $localVarExprs
     */
    private function resolveLocalVarExpr(Expr $expr, array $localVarExprs): ?Expr
    {
        if (! $expr instanceof Variable) {
            return null;
        }

        $varName = $expr->name;
        if (! is_string($varName)) {
            return null;
        }

        return $localVarExprs[$varName] ?? null;
    }

    /**
     * Builds a synthetic Expr from a @var PHPDoc comment for the given property name.
     *
     * @param  array<string, string>  $useMap
     */
    private function resolveExprFromDocComment(?Doc $docComment, string $propertyName, array $useMap): ?Expr
    {
        if ($docComment === null) {
            return null;
        }

        if (! preg_match('/@var\s+([\w\\\\]+)(?:\s+\$(\w+))?/', $docComment->getText(), $matches)) {
            return null;
        }

        $annotatedVar = $matches[2] ?? null;

        if ($annotatedVar !== null && $annotatedVar !== $propertyName) {
            return null;
        }

        return $this->buildSyntheticExprFromTypeName($matches[1], $useMap);
    }

    /**
     * Creates a synthetic Expr node from a type name that Scope::getType() resolves to the intended type.
     *
     * @param  array<string, string>  $useMap
     */
    private function buildSyntheticExprFromTypeName(string $typeName, array $useMap): ?Expr
    {
        return match (strtolower($typeName)) {
            'string' => new String_(''),
            'int', 'integer' => new Int_(0),
            'float', 'double' => new Float_(0.0),
            'bool', 'boolean' => new ConstFetch(new Name\FullyQualified('true')),
            'null' => new ConstFetch(new Name\FullyQualified('null')),
            'array' => new Array_([]),
            'mixed', '' => null,
            default => new New_(new FullyQualified($this->resolveFqcn($typeName, $useMap))),
        };
    }

    /**
     * Resolves a short class name to its FQCN using the file's use map.
     *
     * @param  array<string, string>  $useMap
     */
    private function resolveFqcn(string $name, array $useMap): string
    {
        if (str_starts_with($name, '\\')) {
            return ltrim($name, '\\');
        }

        $firstSegment = explode('\\', $name)[0];

        if (isset($useMap[$firstSegment])) {
            if (str_contains($name, '\\')) {
                return $useMap[$firstSegment] . substr($name, strlen($firstSegment));
            }

            return $useMap[$firstSegment];
        }

        return $name;
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
     * Parses a PHP file into AST with name resolution, returning statements and a use alias map.
     *
     * @return array{Node[], array<string, string>}|null
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

        $stmts = $traverser->traverse($stmts);
        $useMap = $this->extractUseMap($stmts);

        return [$stmts, $useMap];
    }

    /**
     * Builds an alias→FQCN map from use statements in the parsed AST.
     *
     * @param  Node[]  $stmts
     * @return array<string, string>
     */
    private function extractUseMap(array $stmts): array
    {
        $useMap = [];

        foreach ($stmts as $stmt) {
            if ($stmt instanceof Use_) {
                foreach ($stmt->uses as $use) {
                    $alias = $use->alias !== null ? $use->alias->name : $use->name->getLast();
                    $useMap[$alias] = $use->name->toString();
                }
            }

            if ($stmt instanceof Namespace_) {
                foreach ($stmt->stmts as $namespacedStmt) {
                    if ($namespacedStmt instanceof Use_) {
                        foreach ($namespacedStmt->uses as $use) {
                            $alias = $use->alias !== null ? $use->alias->name : $use->name->getLast();
                            $useMap[$alias] = $use->name->toString();
                        }
                    }
                }
            }
        }

        return $useMap;
    }

    /**
     * Extracts string arguments from a method call.
     *
     * @return string[]
     */
    private function extractStringArgs(MethodCall $methodCall): array
    {
        $strings = [];

        foreach ($methodCall->getArgs() as $arg) {
            $value = $arg->value;
            if ($value instanceof String_) {
                $strings[] = $value->value;
            }
        }

        return $strings;
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
