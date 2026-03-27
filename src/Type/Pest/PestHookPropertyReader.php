<?php

declare(strict_types=1);

namespace PestStan\Type\Pest;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
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
            $properties[$name] = isset($properties[$name]) ? array_merge($properties[$name], $exprs) : $exprs;
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

        $this->extractUsesBeforeEachProperties($nodeFinder, $stmts, $useMap, $filePath);
    }

    /**
     * Extracts property expressions from uses()->beforeEach(Closure)->in() chains
     * and stores them under the directory of the Pest.php file.
     *
     * @param  Node[]  $stmts
     * @param  array<string, string>  $useMap
     */
    private function extractUsesBeforeEachProperties(NodeFinder $nodeFinder, array $stmts, array $useMap, string $pestFilePath): void
    {
        $directoryPath = $this->normalizePath(dirname($pestFilePath)) . '/';

        /** @var MethodCall[] $methodCalls */
        $methodCalls = $nodeFinder->findInstanceOf($stmts, MethodCall::class);

        foreach ($methodCalls as $methodCall) {
            if (! $this->isMethodNamed($methodCall, 'beforeEach')) {
                continue;
            }

            if (! $this->isUsesChain($methodCall->var)) {
                continue;
            }

            foreach ($methodCall->getArgs() as $arg) {
                if (! $arg->value instanceof Closure) {
                    continue;
                }

                $assigned = $this->extractPropertyAssignments($arg->value, $useMap);
                foreach ($assigned as $name => $exprs) {
                    if (! isset($this->directoryPropertyMap[$directoryPath][$name])) {
                        $this->directoryPropertyMap[$directoryPath][$name] = [];
                    }

                    foreach ($exprs as $expr) {
                        $this->directoryPropertyMap[$directoryPath][$name][] = $expr;
                    }
                }

                break;
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

            foreach ($funcCall->getArgs() as $arg) {
                if (! $arg->value instanceof Closure) {
                    continue;
                }

                $assigned = $this->extractPropertyAssignments($arg->value, $useMap);
                foreach ($assigned as $name => $exprs) {
                    if (! isset($properties[$name])) {
                        $properties[$name] = [];
                    }

                    foreach ($exprs as $expr) {
                        $properties[$name][] = $expr;
                    }
                }

                break;
            }
        }

        return $properties;
    }

    /**
     * Extracts $this->prop = expr assignments from a hook closure.
     *
     * @param  array<string, string>  $useMap
     * @return array<string, list<Expr>>
     */
    private function extractPropertyAssignments(Closure $closure, array $useMap): array
    {
        $properties = [];
        $localVarMap = $this->buildLocalVarExprMap($closure, $useMap);

        foreach ($closure->stmts as $stmt) {
            if (! $stmt instanceof Expression) {
                continue;
            }

            $expr = $stmt->expr;
            if (! $expr instanceof Assign) {
                continue;
            }

            $var = $expr->var;
            if (! $var instanceof PropertyFetch) {
                continue;
            }

            if (! $var->var instanceof Variable) {
                continue;
            }

            if ($var->var->name !== 'this') {
                continue;
            }

            if (! $var->name instanceof Identifier) {
                continue;
            }

            $propName = $var->name->name;
            $rhsExpr = $expr->expr;

            if ($rhsExpr instanceof Variable && is_string($rhsExpr->name)) {
                $varName = $rhsExpr->name;
                if (isset($localVarMap[$varName])) {
                    $rhsExpr = $localVarMap[$varName];
                }
            }

            $docComment = $stmt->getDocComment();
            if ($docComment instanceof Doc) {
                $syntheticExpr = $this->resolveExprFromDocComment($docComment->getText(), null, $useMap);
                if ($syntheticExpr instanceof Expr) {
                    $rhsExpr = $syntheticExpr;
                }
            }

            $properties[$propName][] = $rhsExpr;
        }

        return $properties;
    }

    /**
     * Builds a map of local variable name to RHS Expr, respecting @var doc comments.
     *
     * @param  array<string, string>  $useMap
     * @return array<string, Expr>
     */
    private function buildLocalVarExprMap(Closure $closure, array $useMap): array
    {
        $map = [];

        foreach ($closure->stmts as $stmt) {
            if (! $stmt instanceof Expression) {
                continue;
            }

            $expr = $stmt->expr;
            if (! $expr instanceof Assign) {
                continue;
            }

            $var = $expr->var;
            if (! $var instanceof Variable) {
                continue;
            }

            if (! is_string($var->name)) {
                continue;
            }

            $varName = $var->name;
            $docComment = $stmt->getDocComment();

            if ($docComment instanceof Doc) {
                $syntheticExpr = $this->resolveExprFromDocComment($docComment->getText(), $varName, $useMap);
                if ($syntheticExpr instanceof Expr) {
                    $map[$varName] = $syntheticExpr;

                    continue;
                }
            }

            $map[$varName] = $expr->expr;
        }

        return $map;
    }

    /**
     * Parses a @var doc comment and returns a synthetic Expr for the type, if resolvable.
     *
     * @param  array<string, string>  $useMap
     */
    private function resolveExprFromDocComment(string $docText, ?string $varName, array $useMap): ?Expr
    {
        if (! preg_match('/@var\s+([\\\\\w]+)(?:\s+\$(\w+))?/', $docText, $matches)) {
            return null;
        }

        $typeName = $matches[1];
        $docVarName = $matches[2] ?? null;

        if ($varName !== null && $docVarName !== null && $docVarName !== $varName) {
            return null;
        }

        return $this->buildSyntheticExprFromTypeName($typeName, $useMap);
    }

    /**
     * Builds a New_ node for a class type name resolved through the use map.
     *
     * @param  array<string, string>  $useMap
     */
    private function buildSyntheticExprFromTypeName(string $typeName, array $useMap): ?Expr
    {
        $fqcn = $useMap[$typeName] ?? $typeName;

        $builtIns = ['string', 'int', 'float', 'bool', 'null', 'array', 'object', 'mixed', 'void', 'never'];
        if (in_array(strtolower($fqcn), $builtIns, true)) {
            return null;
        }

        return new New_(new FullyQualified($fqcn));
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

    private function isMethodNamed(MethodCall $methodCall, string $name): bool
    {
        return $methodCall->name instanceof Identifier && $methodCall->name->toString() === $name;
    }

    private function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }
}
