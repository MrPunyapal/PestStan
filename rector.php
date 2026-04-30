<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php82\Rector\Class_\ReadOnlyClassRector;
use RectorPest\Set\PestLevelSetList;
use RectorPest\Set\PestSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withRootFiles()
    ->withSkip([
        ReadOnlyClassRector::class,
        __DIR__ . '/tests/Type/data',
        __DIR__ . '/tests/Rules/data',
    ])
    ->withSets([
        PestLevelSetList::UP_TO_PEST_30,
        PestSetList::PEST_CODE_QUALITY,
        PestSetList::PEST_CHAIN,
    ])
    ->withImportNames(removeUnusedImports: true)
    ->withPhpSets(php82: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        instanceOf: true,
        earlyReturn: true,
    );
