# PestStan

PHPStan extension for [Pest PHP](https://pestphp.com/) testing framework. Provides type-safe expectations, proper `$this` binding in test closures, and accurate return types for all Pest functions.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mrpunyapal/peststan.svg?style=flat-square)](https://packagist.org/packages/mrpunyapal/peststan)
[![Total Downloads](https://img.shields.io/packagist/dt/mrpunyapal/peststan.svg?style=flat-square)](https://packagist.org/packages/mrpunyapal/peststan)
[![CI](https://github.com/mrpunyapal/peststan/actions/workflows/ci.yml/badge.svg)](https://github.com/mrpunyapal/peststan/actions/workflows/ci.yml)

## Requirements

- PHP ^8.2
- PHPStan ^2.0
- Pest PHP ^3.0 or ^4.0

## Installation

```bash
composer require --dev mrpunyapal/peststan
```

If you have [phpstan/extension-installer](https://github.com/phpstan/extension-installer) (recommended), the extension is registered automatically.

Otherwise, add it manually to your `phpstan.neon` or `phpstan.neon.dist`:

```neon
includes:
    - vendor/mrpunyapal/peststan/extension.neon
```

## Features

### Generic `expect()` Function

The extension provides generic type inference for Pest's `expect()` function, so PHPStan knows the exact type of the expectation value:

```php
expect('hello');           // Expectation<string>
expect(42);                // Expectation<int>
expect(['a' => 1]);        // Expectation<array{a: int}>
expect($user);             // Expectation<User>
expect();                  // Expectation<null>
```

### Type Narrowing Assertions

Type-checking assertion methods narrow the generic type parameter, so PHPStan tracks the type through assertion chains:

```php
/** @var int|string $value */
$value = getValue();

expect($value)->toBeString();
// PHPStan now knows the expectation wraps a string

expect($value)->toBeInstanceOf(User::class);
// PHPStan now knows the expectation wraps a User
```

Supported type-narrowing assertions:
`toBeString`, `toBeInt`, `toBeFloat`, `toBeBool`, `toBeArray`, `toBeList`, `toBeObject`, `toBeCallable`, `toBeIterable`, `toBeNumeric`, `toBeScalar`, `toBeResource`, `toBeTrue`, `toBeFalse`, `toBeNull`, `toBeInstanceOf`.

### Type-Safe `and()` Chaining

The `and()` method properly changes the generic type parameter, enabling type-safe assertion chains:

```php
expect('hello')
    ->toBeString()       // Expectation<string>
    ->and(42)            // Expectation<int>
    ->toBeInt()          // Expectation<int>
    ->and(['a', 'b'])    // Expectation<array{string, string}>
    ->toHaveCount(2);    // Expectation<array{string, string}>
```

### `$this` Binding in Test Closures

The extension ensures `$this` is properly typed inside all Pest test closures and lifecycle hooks. It auto-detects your TestCase class from your `Pest.php` configuration file:

```php
// tests/Pest.php
uses(Tests\TestCase::class)->in('Feature');

// tests/Feature/ExampleTest.php
it('can access test case methods', function () {
    $this->get('/');  // PHPStan knows $this is Tests\TestCase
});

beforeEach(function () {
    $this->assertTrue(true);   // Works in hooks too
});
```

Supported functions: `it()`, `test()`, `describe()`, `beforeEach()`, `afterEach()`, `beforeAll()`, `afterAll()`.

### Dynamic Properties in Test Closures

Pest allows setting and accessing arbitrary properties on `$this` inside test closures. The extension supports this by treating all undefined properties on TestCase subclasses as `mixed`:

```php
beforeEach(function () {
    $this->user = User::factory()->create();  // No error
});

it('can access dynamic props', function () {
    $this->user;  // mixed - no "undefined property" error
});
```

## Configuration

### Automatic TestCase Detection

PestStan reads your `Pest.php` files to determine which TestCase class is used in each test directory. It supports both `uses()` and `pest()->extend()` patterns:

```php
// uses(TestCase::class)->in('Feature');
// pest()->extend(TestCase::class)->in('Unit');
```

No configuration needed — it discovers `Pest.php` files automatically from your PHPStan `paths`.

### Manual TestCase Override

If auto-detection doesn't work for your setup, or you want a global default, set it in your `phpstan.neon`:

```neon
parameters:
    peststan:
        testCaseClass: App\Testing\TestCase
```

### Explicit Pest.php Paths

If your `Pest.php` files aren't within PHPStan's analysis paths, you can specify them explicitly:

```neon
parameters:
    peststan:
        pestConfigFiles:
            - tests/Pest.php
```

### Pest Function Return Types

Accurate return types for all Pest global functions:

| Function | Return Type |
|----------|-------------|
| `expect($value)` | `Expectation<TValue>` |
| `it()` / `test()` / `todo()` | `TestCall` |
| `describe()` | `DescribeCall` |

### `not()` and `each()` Return Types

```php
expect('hello')->not();    // OppositeExpectation<string>
expect([1, 2])->each();    // EachExpectation<array{int, int}>
```

### TestCall Chaining

All `TestCall` methods are properly typed for fluent chaining:

```php
it('does something', function () { /* ... */ })
    ->with(['a', 'b'])
    ->group('unit', 'feature')
    ->skip(false)
    ->depends('other test')
    ->throws(RuntimeException::class)
    ->repeat(3);
```

### Architecture Testing Support

Architecture testing methods are fully supported:

```php
expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model')
    ->ignoring('App\Models\Legacy');

expect('App')
    ->classes()
    ->toBeFinal();

expect('App\Actions')->toBeInvokable();
expect('App\DTOs')->toBeReadonly();
expect('App')->toUseStrictTypes();
```

## Testing

```bash
composer test        # Run all checks (lint + types + unit)
composer lint        # Apply code style fixes (Rector + Pint)
composer test:lint   # Check code style (dry-run)
composer test:types  # Run PHPStan analysis
composer test:unit   # Run Pest unit tests
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

MIT License. See [LICENSE](LICENSE) for more information.

## Credits

- Built for [Pest PHP](https://pestphp.com/)
- Powered by [PHPStan](https://phpstan.org/)
