<?php

declare(strict_types=1);

namespace PestStan\Analysis\Expectation;

final class MatcherCategoryRegistry
{
    public const TYPE_ASSERTION = 'type_assertion';

    public const COLLECTION = 'collection_matcher';

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
        'each' => [self::COLLECTION, self::ITERABLE],
        'sequence' => [self::COLLECTION, self::ITERABLE],
        'toContainEqual' => [self::COLLECTION, self::ITERABLE],
        'toContainOnlyInstancesOf' => [self::COLLECTION, self::ITERABLE],
        'toHaveCount' => [self::COLLECTION, self::ITERABLE],
        'toHaveSameSize' => [self::COLLECTION, self::ITERABLE],
        'toBeString' => [self::TYPE_ASSERTION, self::STRING, self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeInt' => [self::TYPE_ASSERTION, self::NUMERIC, self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeFloat' => [self::TYPE_ASSERTION, self::NUMERIC, self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeBool' => [self::TYPE_ASSERTION, self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeTrue' => [self::TYPE_ASSERTION, self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeFalse' => [self::TYPE_ASSERTION, self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeNull' => [self::TYPE_ASSERTION, self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeArray' => [self::TYPE_ASSERTION, self::COLLECTION, self::ITERABLE, self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeList' => [self::TYPE_ASSERTION, self::COLLECTION, self::ITERABLE, self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeObject' => [self::TYPE_ASSERTION, self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeCallable' => [self::TYPE_ASSERTION, self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeIterable' => [self::TYPE_ASSERTION, self::COLLECTION, self::ITERABLE, self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeNumeric' => [self::TYPE_ASSERTION, self::NUMERIC, self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeScalar' => [self::TYPE_ASSERTION, self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeInstanceOf' => [self::TYPE_ASSERTION, self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
        'toBeResource' => [self::TYPE_ASSERTION, self::SEMANTIC_ASSERTION, self::STATE_ASSERTION],
    ];

    /** @var list<string> */
    private const PRIMARY_CATEGORY_ORDER = [
        self::TYPE_ASSERTION,
        self::COLLECTION,
        self::STRING,
        self::NUMERIC,
        self::ITERABLE,
        self::FILESYSTEM,
    ];

    /**
     * @return list<string>
     */
    public function categoriesFor(string $methodName): array
    {
        return self::METHOD_CATEGORIES[$methodName] ?? [];
    }

    public function primaryCategoryFor(string $methodName): ?string
    {
        $categories = $this->categoriesFor($methodName);

        foreach (self::PRIMARY_CATEGORY_ORDER as $category) {
            if (in_array($category, $categories, true)) {
                return $category;
            }
        }

        return $categories[0] ?? null;
    }
}
