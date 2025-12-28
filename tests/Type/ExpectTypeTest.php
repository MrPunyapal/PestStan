<?php

declare(strict_types=1);

use function PHPStan\Testing\assertType;

it('properly types expect() return value', function () {
    $string = 'hello';
    $result = expect($string);
    
    assertType('Pest\Expectation<string>', $result);
});

it('properly types $this in test closures', function () {
    assertType('PHPUnit\Framework\TestCase', $this);
    
    $this->assertTrue(true);
});

test('expect with integer', function () {
    $number = 123;
    $result = expect($number);
    
    assertType('Pest\Expectation<int>', $result);
});

test('expect with object', function () {
    $object = new stdClass();
    $result = expect($object);
    
    assertType('Pest\Expectation<stdClass>', $result);
});
