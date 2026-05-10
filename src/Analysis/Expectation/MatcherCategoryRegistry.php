<?php

declare(strict_types=1);

namespace PestStan\Analysis\Expectation;

final class MatcherCategoryRegistry
{
    public const STRING = 'string_matcher';

    public const ITERABLE = 'iterable_matcher';

    public const FILESYSTEM = 'filesystem_matcher';

    public const NUMERIC = 'numeric_matcher';

    public const SEMANTIC_ASSERTION = 'semantic_assertion';

    public const STATE_ASSERTION = 'state_assertion';

    /** @var array<string, list<string>> */
    private const METHOD_CATEGORIES = [
        'json' => [self::STRING],
        'toStartWith' => [self::STRING],
        'toEndWith' => [self::STRING],
        'toBeJson' => [self::STRING],
        'toBeUppercase' => [self::STRING, self::STATE_ASSERTION],
        'toBeLowercase' => [self::STRING, self::STATE_ASSERTION],
        'toBeAlphaNumeric' => [self::STRING, self::NUMERIC, self::STATE_ASSERTION],
        'toBeAlpha' => [self::STRING, self::STATE_ASSERTION],
        'toBeDigits' => [self::STRING, self::NUMERIC, self::STATE_ASSERTION],
        'toBeSnakeCase' => [self::STRING, self::STATE_ASSERTION],
        'toBeKebabCase' => [self::STRING, self::STATE_ASSERTION],
        'toBeCamelCase' => [self::STRING, self::STATE_ASSERTION],
        'toBeStudlyCase' => [self::STRING, self::STATE_ASSERTION],
        'toBeUuid' => [self::STRING, self::STATE_ASSERTION],
        'toBeUrl' => [self::STRING, self::STATE_ASSERTION],
        'toBeSlug' => [self::STRING, self::STATE_ASSERTION],
        'toMatch' => [self::STRING],
        'toBeDirectory' => [self::FILESYSTEM],
        'toBeFile' => [self::FILESYSTEM],
        'toBeReadableFile' => [self::FILESYSTEM],
        'toBeWritableFile' => [self::FILESYSTEM],
        'toBeReadableDirectory' => [self::FILESYSTEM],
        'toBeWritableDirectory' => [self::FILESYSTEM],
        'each' => [self::ITERABLE],
        'sequence' => [self::ITERABLE],
        'toContainEqual' => [self::ITERABLE],
        'toContainOnlyInstancesOf' => [self::ITERABLE],
        'toHaveCount' => [self::ITERABLE],
        'toHaveSameSize' => [self::ITERABLE],
        'toBeString' => [self::STRING, self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeInt' => [self::NUMERIC, self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeFloat' => [self::NUMERIC, self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeBool' => [self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeTrue' => [self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeFalse' => [self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeNull' => [self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeArray' => [self::ITERABLE, self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeList' => [self::ITERABLE, self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeObject' => [self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeCallable' => [self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeIterable' => [self::ITERABLE, self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeNumeric' => [self::NUMERIC, self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeScalar' => [self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeInstanceOf' => [self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeResource' => [self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
    ];

    /**
     * @return list<string>
     */
    public function categoriesFor(string $methodName): array
    {
        return self::METHOD_CATEGORIES[$methodName] ?? [];
    }
}
