# PestStan

PHPStan extension for [Pest PHP](https://pestphp.com/) testing framework.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mrpunyapal/peststan.svg?style=flat-square)](https://packagist.org/packages/mrpunyapal/peststan)
[![Total Downloads on Packagist](https://img.shields.io/packagist/dt/mrpunyapal/peststan.svg?style=flat-square)](https://packagist.org/packages/mrpunyapal/peststan)
[![CI](https://github.com/mrpunyapal/peststan/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/mrpunyapal/peststan/actions/workflows/ci.yml)

## Features

- **Generic `expect()` function**: Type-safe expectations with `Expectation<TValue>`
- **Test closure `$this` binding**: Proper type inference for `$this` in test closures
- **Pest function stubs**: Complete type definitions for all Pest global functions

## Installation

```bash
composer require --dev mrpunyapal/peststan
```

If you have [phpstan/extension-installer](https://github.com/phpstan/extension-installer) installed, the extension will be automatically registered.

Otherwise, you need to include the extension manually in your `phpstan.neon` or `phpstan.neon.dist`:

```neon
includes:
    - vendor/mrpunyapal/peststan/extension.neon
```

## What it does

### Generic `expect()` function

The extension provides generic type inference for Pest's `expect()` function:

```php
expect('hello')->toBeString(); // PHPStan knows this is Expectation<string>
expect(42)->toBeInt();         // PHPStan knows this is Expectation<int>
expect($user)->toBeInstanceOf(User::class); // PHPStan knows this is Expectation<User>
```

### Test closure binding

The extension ensures `$this` is properly typed as `PHPUnit\Framework\TestCase` in test closures:

```php
it('has proper $this type', function () {
    // PHPStan knows $this is PHPUnit\Framework\TestCase
    $this->assertTrue(true);
    $this->assertSame('expected', $actual);
});

test('can use TestCase methods', function () {
    $this->markTestSkipped('Skipping this test');
});

beforeEach(function () {
    // Hooks also have proper $this binding
    $this->createApplication();
});
```

### Pest function stubs

Complete type definitions for Pest functions including:

- `it()`, `test()`, `describe()`
- `beforeEach()`, `afterEach()`, `beforeAll()`, `afterAll()`
- `uses()`, `pest()`
- `expect()` with full `Expectation` class methods

## Testing

```bash
composer test      # Run all tests (lint + types + unit)
composer lint      # Apply code style fixes
composer test:lint # Check code style (dry-run)
composer test:types # Run PHPStan analysis
composer test:unit # Run Pest unit tests
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

MIT License. See [LICENSE](LICENSE) for more information.

## Credits

- Built for [Pest PHP](https://pestphp.com/)
- Powered by [PHPStan](https://phpstan.org/)
