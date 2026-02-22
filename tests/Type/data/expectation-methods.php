<?php

declare(strict_types=1);

namespace ExpectationMethods;

use function PHPStan\Testing\assertType;

use stdClass;

function testExpectReturnType(): void
{
    /** @var string $str */
    $str = 'test';
    assertType('Pest\Expectation<string>', expect($str));

    /** @var int $num */
    $num = 42;
    assertType('Pest\Expectation<int>', expect($num));

    assertType('Pest\Expectation<array{}>', expect([]));
}

function testValueProperty(): void
{
    /** @var string $str */
    $str = 'hello';
    $expectation = expect($str);
    assertType('string', $expectation->value);

    /** @var int $num */
    $num = 42;
    $intExpectation = expect($num);
    assertType('int', $intExpectation->value);
}

function testAndMethod(): void
{
    /** @var string $str */
    $str = 'hello';
    $result = expect($str)->and(42);
    assertType('Pest\Expectation<int>', $result);

    /** @var int $num */
    $num = 1;
    $result2 = expect($num)->and('world');
    assertType('Pest\Expectation<string>', $result2);
}

function testNotMethod(): void
{
    /** @var string $str */
    $str = 'hello';
    $result = expect($str)->not();
    assertType('Pest\Expectations\OppositeExpectation<string>', $result);
}

function testEachMethod(): void
{
    /** @var array{int, int, int} $arr */
    $arr = [1, 2, 3];
    $result = expect($arr)->each();
    assertType('Pest\Expectations\EachExpectation<array{int, int, int}>', $result);
}

function testJsonMethod(): void
{
    $result = expect('{"key": "value"}')->json();
    assertType('Pest\Expectation<array<int|string, mixed>|bool>', $result);
}

function testDumpMethod(): void
{
    /** @var string $str */
    $str = 'hello';
    $result = expect($str)->dump();
    assertType('Pest\Expectation<string>', $result);
}

function testDdMethod(): void
{
    /** @var string $str */
    $str = 'hello';
    assertType('never', expect($str)->dd());
}

function testToBeStringNarrows(): void
{
    /** @var int|string $value */
    $value = 'hello';
    $result = expect($value)->toBeString();
    assertType('Pest\Expectation<string>', $result);
    assertType('string', $result->value);
}

function testToBeIntNarrows(): void
{
    /** @var int|string $value */
    $value = 42;
    $result = expect($value)->toBeInt();
    assertType('Pest\Expectation<int>', $result);
    assertType('int', $result->value);
}

function testToBeFloatNarrows(): void
{
    /** @var int|float $value */
    $value = 3.14;
    $result = expect($value)->toBeFloat();
    assertType('Pest\Expectation<float>', $result);
}

function testToBeBoolNarrows(): void
{
    /** @var bool|string $value */
    $value = true;
    $result = expect($value)->toBeBool();
    assertType('Pest\Expectation<bool>', $result);
}

function testToBeArrayNarrows(): void
{
    /** @var array<string, int>|string $value */
    $value = ['a' => 1];
    $result = expect($value)->toBeArray();
    assertType('Pest\Expectation<array<int|string, mixed>>', $result);
}

function testToBeNullNarrows(): void
{
    /** @var string|null $value */
    $value = null;
    $result = expect($value)->toBeNull();
    assertType('Pest\Expectation<null>', $result);
    assertType('null', $result->value);
}

function testToBeTrueNarrows(): void
{
    /** @var bool $value */
    $value = true;
    $result = expect($value)->toBeTrue();
    assertType('Pest\Expectation<true>', $result);
}

function testToBeFalseNarrows(): void
{
    /** @var bool $value */
    $value = false;
    $result = expect($value)->toBeFalse();
    assertType('Pest\Expectation<false>', $result);
}

/** @return stdClass|string */
function getObjectOrString()
{
    return rand(0, 1) === 1 ? new stdClass() : 'hello';
}

function testToBeInstanceOfNarrows(): void
{
    $value = getObjectOrString();
    $result = expect($value)->toBeInstanceOf(stdClass::class);
    assertType('Pest\Expectation<stdClass>', $result);
    assertType('stdClass', $result->value);
}

function testToBeObjectNarrows(): void
{
    $value = getObjectOrString();
    $result = expect($value)->toBeObject();
    assertType('Pest\Expectation<object>', $result);
}

function testChaining(): void
{
    /** @var string $str */
    $str = 'hello';
    $result = expect($str)
        ->toBe('hello')
        ->toBeString()
        ->toHaveLength(5);
    assertType('Pest\Expectation<string>', $result);
}

function testChainingWithAnd(): void
{
    /** @var string $str */
    $str = 'hello';
    $result = expect($str)
        ->toBeString()
        ->and(42)
        ->toBeInt();
    assertType('Pest\Expectation<int>', $result);
}

function testAssertionMethodsReturnSelf(): void
{
    /** @var string $str */
    $str = 'hello';
    assertType('Pest\Expectation<string>', expect($str)->toBe('hello'));
    assertType('Pest\Expectation<string>', expect($str)->toEqual('hello'));
    assertType('Pest\Expectation<string>', expect($str)->toContain('ell'));
    assertType('Pest\Expectation<string>', expect($str)->toStartWith('he'));
    assertType('Pest\Expectation<string>', expect($str)->toEndWith('lo'));
    assertType('Pest\Expectation<string>', expect($str)->toHaveLength(5));
    assertType('Pest\Expectation<string>', expect($str)->toMatch('/hello/'));
    assertType('Pest\Expectation<string>', expect($str)->toBeUppercase());
    assertType('Pest\Expectation<string>', expect($str)->toBeLowercase());
}

function testComparisonMethods(): void
{
    /** @var int $num */
    $num = 5;
    assertType('Pest\Expectation<int>', expect($num)->toBeGreaterThan(3));
    assertType('Pest\Expectation<int>', expect($num)->toBeGreaterThanOrEqual(5));
    assertType('Pest\Expectation<int>', expect($num)->toBeLessThan(10));
    assertType('Pest\Expectation<int>', expect($num)->toBeLessThanOrEqual(5));
    assertType('Pest\Expectation<int>', expect($num)->toBeBetween(1, 10));
}

function testCollectionMethods(): void
{
    /** @var array<string, int> $data */
    $data = ['a' => 1, 'b' => 2];
    assertType('Pest\Expectation<array<string, int>>', expect($data)->toHaveKey('a'));
    assertType('Pest\Expectation<array<string, int>>', expect($data)->toHaveKeys(['a', 'b']));
    assertType('Pest\Expectation<array<string, int>>', expect($data)->toHaveCount(2));
    assertType('Pest\Expectation<array<string, int>>', expect($data)->toHaveProperty('a'));
}

function testWhenMethod(): void
{
    /** @var string $str */
    $str = 'hello';
    $result = expect($str)->when(true, function ($e) {
        return $e->toBe('hello');
    });
    assertType('Pest\Expectation<string>', $result);
}

function testToBeListNarrows(): void
{
    /** @var array<mixed>|string $value */
    $value = [1, 2, 3];
    $result = expect($value)->toBeList();
    assertType('Pest\Expectation<list>', $result);
}
