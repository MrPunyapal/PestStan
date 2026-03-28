<?php

declare(strict_types=1);

namespace PestStan\Type\Pest;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;

/**
 * Shared utilities for discovering Pest.php files, parsing PHP files, and normalizing paths.
 */
final class PestFileDiscoverer
{
    private readonly Parser $parser;

    /**
     * @param  string[]  $scanPaths  PHPStan's configured analysis paths
     */
    public function __construct(
        private readonly array $scanPaths,
    ) {
        $this->parser = (new ParserFactory)->createForNewestSupportedVersion();
    }

    /**
     * Discovers Pest.php files from scan paths and optional explicit file paths.
     *
     * @param  string[]  $extraFiles  Additional explicit Pest.php file paths
     * @return string[]
     */
    public function discoverPestFiles(array $extraFiles = []): array
    {
        $files = [];

        foreach ($extraFiles as $configFile) {
            $realPath = realpath($configFile);
            if ($realPath !== false && is_file($realPath)) {
                $files[] = $realPath;
            }
        }

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
     * Parses a PHP file into AST with name resolution, returning statements and a use alias map.
     *
     * @return array{Node[], array<string, string>}|null
     */
    public function parseFile(string $filePath): ?array
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return null;
        }

        $stmts = $this->parser->parse($content);
        if ($stmts === null) {
            return null;
        }

        $traverser = new NodeTraverser;
        $traverser->addVisitor(new NameResolver);

        $stmts = $traverser->traverse($stmts);
        $useMap = $this->extractUseMap($stmts);

        return [$stmts, $useMap];
    }

    public function normalizePath(string $path): string
    {
        $realPath = realpath($path);
        if ($realPath !== false) {
            return str_replace('\\', '/', $realPath);
        }

        return str_replace('\\', '/', $path);
    }

    public function isMethodNamed(MethodCall $methodCall, string $name): bool
    {
        return $methodCall->name instanceof Identifier && $methodCall->name->toString() === $name;
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
}
