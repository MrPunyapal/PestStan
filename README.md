# PestStan

PHPStan extension for [Pest PHP](https://pestphp.com/) testing framework. Provides type-safe expectations, proper `$this` binding in test closures, and accurate return types for all Pest functions.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mrpunyapal/peststan.svg?style=flat-square)](https://packagist.org/packages/mrpunyapal/peststan)
[![Total Downloads](https://img.shields.io/packagist/dt/mrpunyapal/peststan.svg?style=flat-square)](https://packagist.org/packages/mrpunyapal/peststan)
[![CI](https://github.com/mrpunyapal/peststan/actions/workflows/ci.yml/badge.svg)](https://github.com/mrpunyapal/peststan/actions/workflows/ci.yml)

## Requirements

- PHP ^8.2
- PHPStan ^2.0
- Pest PHP ^3.0, ^4.0, or ^5.0

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

Pest allows setting properties on `$this` inside `beforeEach`/`beforeAll` hooks. The extension reads those assignments and **infers the exact type** — no `@var` annotation or extra local variable required:

```php
beforeEach(function () {
    $this->post   = new Post;                    // Post
    $this->title  = 'Hello';                     // 'Hello' (constant string)
    $this->count  = 42;                          // 42 (constant int)
    $this->active = true;                        // true
});

it('knows the property types', function () {
    $this->post->title;          // PHPStan knows $this->post is Post — no "Cannot access property on mixed" error
    strlen($this->title);        // fine — PHPStan knows it is a string
});
```

For method-call chains such as factory calls, annotate the local variable with `@var` to guide inference:

```php
beforeEach(function () {
    /** @var User $user */
    $user        = User::factory()->create();
    $this->user  = $user;        // User
});
```

If the same property is set by multiple hooks the type is **unioned**:

```php
beforeEach(function () { $this->item = new Post; });
beforeEach(function () { $this->item = new Comment; });

it('sees the union', function () {
    $this->item;  // Post|Comment
});
```

Properties that are never set in a hook remain `mixed`.

## Configuration

### Automatic TestCase Detection

PestStan reads your `Pest.php` files to determine which TestCase class is used in each test directory. It supports the `uses()` pattern:

```php
// uses(TestCase::class)->in('Feature');
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
| `pest()` | `Configuration` |
| `uses(...)` | `UsesCall` |
| `it()` / `test()` / `todo()` | `TestCall` |
| `describe()` | `DescribeCall` |
| `beforeEach()` | `BeforeEachCall` |
| `afterEach()` | `AfterEachCall` |
| `beforeAll()` / `afterAll()` | `null` |
| `dataset()` / `covers()` / `mutates()` | `null` |

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

When you set `peststan.testCaseClass` to a custom class, PestStan also exposes that class's public helper methods on `TestCall` chains:

```php
it('uses a custom helper')->publicHelper();
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

## Static Analysis Rules

PestStan ships with rules that catch common Pest mistakes at static analysis time, before your tests run.

### `pest.emptyTestClosure` — Empty test body

Detects tests whose closure contains no statements.

```php
it('does something'); // fine — todo test
it('does something', function () {});
// ✘ Test 'does something' has an empty closure body. Did you forget to add assertions?
```

### `pest.staticTestClosure` — Static test closure

Pest binds `$this` inside every test closure to the `TestCase` instance. Marking the closure `static` prevents that binding.

```php
it('example', static function () {
// ✘ Test closure passed to it() must not be static.
    expect(true)->toBeTrue();
});
```

### `pest.beforeAllInDescribe` / `pest.afterAllInDescribe` — Lifecycle hooks inside `describe()`

Pest does not support `beforeAll()` or `afterAll()` inside `describe()` blocks — calling them throws at runtime.

```php
describe('suite', function () {
    beforeAll(function () { /* ... */ });
    // ✘ beforeAll() cannot be used inside describe() blocks.

    afterAll(function () { /* ... */ });
    // ✘ afterAll() cannot be used inside describe() blocks.
});
```

### `pest.repeatInvalidValue` — Invalid `repeat()` count

`repeat()` requires a positive integer greater than zero.

```php
it('runs multiple times', function () { /* ... */ })->repeat(0);
// ✘ repeat() requires a value greater than 0, got 0.
```

### `pest.duplicateTestDescription` — Duplicate test description

Two tests in the same file with the same description will collide at runtime.

```php
it('does something', fn () => expect(1)->toBe(1));
it('does something', fn () => expect(2)->toBe(2));
// ✘ A test with the description 'it does something' already exists in this file.
```

### `pest.impossibleExpectation` — Assertion that always fails

When the static type already makes an assertion impossible, PestStan reports it.

```php
expect(42)->toBeString();
// ✘ Calling toBeString() on Expectation<int> will always fail.

expect('hello')->toBeNull();
// ✘ Calling toBeNull() on Expectation<string> will always fail.
```

Covered assertions: `toBeString`, `toBeInt`, `toBeFloat`, `toBeBool`, `toBeTrue`, `toBeFalse`, `toBeNull`, `toBeArray`, `toBeList`, `toBeObject`, `toBeCallable`, `toBeIterable`, `toBeNumeric`, `toBeScalar`, `toBeInstanceOf`.

### `pest.redundantExpectation` — Assertion that always passes

When the static type already guarantees an assertion will always succeed, the assertion is redundant and adds no value.

```php
expect(true)->toBeTrue();
// ✘ Calling toBeTrue() on Expectation<true> will always pass — the assertion is redundant.

expect('hello')->toBeString();
// ✘ Calling toBeString() on Expectation<string> will always pass — the assertion is redundant.

expect(42)->toBeNumeric();
// ✘ Calling toBeNumeric() on Expectation<int> will always pass — the assertion is redundant.
```

Covered assertions: `toBeString`, `toBeInt`, `toBeFloat`, `toBeBool`, `toBeTrue`, `toBeFalse`, `toBeNull`, `toBeArray`, `toBeList`, `toBeObject`, `toBeCallable`, `toBeIterable`, `toBeNumeric`, `toBeScalar`, `toBeInstanceOf`.

### `pest.expectationRequiresIterable` / `pest.expectationRequiresString` — Incompatible value type

Some expectation methods require the value to satisfy a pre-condition.

```php
expect(42)->each(fn ($e) => $e->toBeInt());
// ✘ Calling each() on Expectation<int> — value is not iterable.

expect(42)->toBeJson();
// ✘ Calling toBeJson() on Expectation<int> — value must be a string.
```

Methods requiring an iterable: `each`, `sequence`.  
Methods requiring a string: `json`, `toStartWith`, `toEndWith`, `toBeJson`, `toBeDirectory`, `toBeFile`, `toBeReadableFile`, `toBeWritableFile`, `toBeReadableDirectory`, `toBeWritableDirectory`.

### `pest.beforeAllThisUsage` — `$this` inside `beforeAll()`

`beforeAll()` runs once in a static context before any tests in the file. `$this` is not available.

```php
beforeAll(function () {
    $this->db = new Database; // ✘ beforeAll() runs in static context — $this is not available. Use beforeEach() instead.
});
```

Use `beforeEach()` to run setup before each test with `$this` available.

### `pest.throwsClassNotFound` / `pest.invalidThrowsException` — Invalid `throws()` argument

`throws()` accepts a class name that implements `Throwable`. Passing a non-existent class or a class that is not `Throwable` is caught at analysis time.

```php
it('fails', function () { ... })->throws('App\NonExistentException');
// ✘ Class App\NonExistentException passed to throws() does not exist.

it('fails', function () { ... })->throws(stdClass::class);
// ✘ throws() expects a Throwable class, got stdClass.
```

### `pest.coversClassNotFound` / `pest.coversFunctionNotFound` — Non-existent symbol in `coversClass()`

`coversClass()`, `coversTrait()`, and `coversFunction()` reference symbols by name. PestStan verifies those symbols exist.

```php
it('covers something', function () { ... })->coversClass('App\Nonexistent\Service');
// ✘ Class App\Nonexistent\Service referenced in coversClass() does not exist.
```

### `pest.describeWithoutTests` — Empty `describe()` block

A `describe()` block that contains no `it()` or `test()` calls (only hooks, or nothing at all) is likely a mistake.

```php
describe('UserService', function () {
    beforeEach(fn () => null);
    // ✘ describe() block 'UserService' contains no tests.
});
```

### `pest.invalidGroupName` — Empty `group()` name

`group()` requires at least one non-empty, non-whitespace string argument.

```php
it('example', fn () => null)->group('');
// ✘ group() requires a non-empty string argument.
```

### Ignoring rules

All rules use PHPStan [identifiers](https://phpstan.org/user-guide/ignoring-errors), so you can suppress them selectively in your baseline or inline:

```neon
# phpstan.neon
parameters:
    ignoreErrors:
        - identifier: pest.emptyTestClosure
```

```php
/** @phpstan-ignore pest.staticTestClosure */
it('example', static fn () => expect(true)->toBeTrue());
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
