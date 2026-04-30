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
use PhpParser\NodeFinder;

/**
 * Parses test files and Pest.php config files to extract dynamic property expressions from beforeEach hooks.
 */
final class PestHookPropertyReader
{
    private const HOOK_FUNCTIONS = ['beforeEach'];

    /** @var array<string, array<string, list<Expr>>> Maps normalized file paths to property name → list of RHS Exprs */
    private array $filePropertyCache = [];

    /** @var array<string, array<string, list<Expr>>> Maps normalized directory paths to property name → list of RHS Exprs */
    private array $directoryPropertyMap = [];

    private bool $pestFilesParsed = false;

    public function __construct(
        private readonly PestFileDiscoverer $fileDiscoverer,
    ) {}

    /**
     * Returns property expressions from hook closures for the given file, to be resolved via Scope::getType().
     *
     * @return array<string, list<Expr>>
     */
    public function getPropertyExprs(string $filePath): array
    {
        $this->ensurePestFilesParsed();

        $normalizedFile = $this->fileDiscoverer->normalizePath($filePath);

        if (! isset($this->filePropertyCache[$normalizedFile])) {
            $this->filePropertyCache[$normalizedFile] = $this->parseTestFile($filePath);
        }

        $properties = $this->filePropertyCache[$normalizedFile];

        $directoryProperties = $this->resolveDirectoryProperties($filePath);
        $this->mergePropertyExprs($properties, $directoryProperties);

        return $properties;
    }

    /**
     * Resolves directory-scoped properties from Pest.php beforeEach hooks.
     *
     * @return array<string, list<Expr>>
     */
    private function resolveDirectoryProperties(string $filePath): array
    {
        $normalizedFile = $this->fileDiscoverer->normalizePath($filePath);

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

        $pestFiles = $this->fileDiscoverer->discoverPestFiles();

        foreach ($pestFiles as $pestFile) {
            $this->parsePestConfigFile($pestFile);
        }
    }

    /**
     * Parses a Pest.php config file to extract beforeEach property assignments from uses() chains.
     */
    private function parsePestConfigFile(string $filePath): void
    {
        $parsed = $this->fileDiscoverer->parseFile($filePath);
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
        $directoryPath = $this->fileDiscoverer->normalizePath(dirname($pestFilePath)) . '/';

        /** @var MethodCall[] $methodCalls */
        $methodCalls = $nodeFinder->findInstanceOf($stmts, MethodCall::class);

        foreach ($methodCalls as $methodCall) {
            if (! $this->fileDiscoverer->isMethodNamed($methodCall, 'beforeEach')) {
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
                if (! isset($this->directoryPropertyMap[$directoryPath])) {
                    $this->directoryPropertyMap[$directoryPath] = [];
                }

                $this->mergePropertyExprs($this->directoryPropertyMap[$directoryPath], $assigned);

                break;
            }
        }
    }

    /**
     * Merges source property expressions into the target map.
     *
     * @param  array<string, list<Expr>>  $target
     * @param  array<string, list<Expr>>  $source
     */
    private function mergePropertyExprs(array &$target, array $source): void
    {
        foreach ($source as $name => $exprs) {
            $target[$name] = isset($target[$name]) ? array_merge($target[$name], $exprs) : $exprs;
        }
    }

    /**
     * Checks if the expression is part of a uses(), pest()->extend(), or pest()->use() call chain.
     */
    private function isUsesChain(Expr $expr): bool
    {
        if ($expr instanceof FuncCall) {
            if (! $expr->name instanceof Name) {
                return false;
            }

            $name = $expr->name->toString();

            return $name === 'uses' || $name === 'pest';
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
        $parsed = $this->fileDiscoverer->parseFile($filePath);
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
                $this->mergePropertyExprs($properties, $assigned);

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

            $thisVariable = $var->var;

            if (! is_string($thisVariable->name)) {
                continue;
            }

            if ($thisVariable->name !== 'this') {
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
        if (! (bool) preg_match('/@var\s+([\\\\\w]+)(?:\s+\$(\w+))?/', $docText, $matches)) {
            return null;
        }

        $typeName = $matches[1];
        $docVarName = $matches[2] ?? null;

        if ($docVarName === 'this') {
            return null;
        }

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
}
