<?php

declare(strict_types=1);

namespace PestStan\Type\Pest;

use FilesystemIterator;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use UnexpectedValueException;

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
        private readonly string $rootDir = '',
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

        $scanPaths = $this->scanPaths;

        if ($this->rootDir !== '') {
            $scanPaths[] = $this->rootDir;
        }

        foreach ($scanPaths as $scanPath) {
            $realPath = realpath($scanPath);
            if ($realPath === false) {
                continue;
            }

            $dir = is_file($realPath) ? dirname($realPath) : $realPath;
            $this->findPestFilesInDirectory($dir, $files);
            $this->findPestFilesInAncestors($dir, $files);
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
     * Recursively finds Pest.php files in a directory using SPL iterators,
     * skipping vendor directories.
     *
     * @param  string[]  $results
     */
    private function findPestFilesInDirectory(string $directory, array &$results): void
    {
        try {
            $directoryIterator = new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS);
            $filterIterator = new RecursiveCallbackFilterIterator(
                $directoryIterator,
                static fn (SplFileInfo $current, string $key, RecursiveDirectoryIterator $iterator): bool => ! $current->isDir() || $current->getFilename() !== 'vendor',
            );
            $iterator = new RecursiveIteratorIterator($filterIterator);
        } catch (UnexpectedValueException) {
            return;
        }

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getFilename() !== 'Pest.php') {
                continue;
            }

            if (! $file->isFile()) {
                continue;
            }

            $realPath = realpath($file->getPathname());
            if ($realPath !== false) {
                $results[] = $realPath;
            }
        }
    }

    /**
     * Walks up from a directory to find Pest.php files in ancestor directories.
     *
     * @param  string[]  $results
     */
    private function findPestFilesInAncestors(string $directory, array &$results): void
    {
        $current = $directory;

        while (true) {
            $parent = dirname($current);

            if ($parent === $current) {
                break;
            }

            $current = $parent;
            $candidate = $current . DIRECTORY_SEPARATOR . 'Pest.php';

            if (is_file($candidate)) {
                $realPath = realpath($candidate);
                if ($realPath !== false) {
                    $results[] = $realPath;
                }
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
