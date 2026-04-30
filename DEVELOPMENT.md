# Development Guide

## Installation

```bash
composer install
```

## Development Commands

### Run All Checks

```bash
composer test
```

This runs in order:
1. Code quality checks (Rector dry-run)
2. Code style checks (Laravel Pint dry-run)
3. Static analysis (PHPStan at max level)
4. Unit tests (Pest)

### Individual Commands

```bash
composer lint          # Apply Rector + Pint fixes
composer test:lint     # Check without applying changes
composer test:types    # Run PHPStan
composer test:unit     # Run Pest tests
```

## Project Structure

```
PestStan/
├── src/Type/Pest/
│   ├── PestFunctionReturnTypeExtension.php            # Pest global helper return types
│   ├── ExpectationMethodReturnTypeExtension.php       # Type narrowing for assertion methods
│   ├── OppositeExpectationMethodReturnTypeExtension.php # not() return types
│   ├── TestClosureThisTypeExtension.php               # $this binding in closures
│   ├── PestConfigReader.php                           # Pest.php auto-detection for TestCase
│   ├── TestCallMethodsClassReflectionExtension.php    # TestCall fluent methods from Pest mixins and custom testCaseClass
│   ├── TestCasePropertiesExtension.php                # Dynamic property support
│   └── PestTestCaseProperty.php                       # PropertyReflection for dynamic props
├── tests/
│   ├── Pest.php                                       # Pest bootstrap
│   ├── TestCase.php                                   # Base test case
│   ├── CustomTestCaseTestCase.php                     # Test case for custom TestCase config
│   ├── extension.neon                                 # Test PHPStan config
│   └── Type/
│       ├── ExpectTypeTest.php                         # Main test runner
│       ├── CustomTestCaseTest.php                     # Custom TestCase test runner
│       ├── Fixtures/
│       │   └── CustomTestCase.php                     # Custom test case fixture for $this and TestCall method coverage
│       ├── custom-testcase-extension.neon             # PHPStan config for custom TestCase tests
│       └── data/
│           ├── expect-function.php                    # expect() return type tests
│           ├── expectation-methods.php                # Assertion method type tests
│           ├── test-closures.php                      # $this binding + dynamic property tests
│           ├── test-closures-custom-testcase.php      # Custom TestCase $this binding tests
│           ├── test-call-methods.php                  # TestCall chaining tests
│           ├── pest-functions.php                     # Pest global helper return type tests
│           └── arch-expectations.php                  # Architecture testing type tests
├── extension.neon                                     # PHPStan extension config
├── composer.json
├── phpstan.neon.dist
├── phpunit.xml.dist
├── pint.json
└── rector.php
```

## How It Works

All type information is provided through PHPStan dynamic type extensions (no stubs).

### 1. PestFunctionReturnTypeExtension

`DynamicFunctionReturnTypeExtension` that overrides return types for Pest global helpers:

- `expect($value)` → `Expectation<typeof $value>` (removes `|null` from Pest's phpdoc)
- `pest()` → `Configuration`
- `uses(...)` → `UsesCall`
- `it()` / `test()` / `todo()` → `TestCall`
- `describe()` → `DescribeCall`
- `beforeEach()` / `afterEach()` → pending call wrappers
- `fixture()` → `string` when the installed Pest version provides the helper
- `beforeAll()` / `afterAll()` / `dataset()` / `covers()` / `mutates()` → `null`

### 2. ExpectationMethodReturnTypeExtension

`DynamicMethodReturnTypeExtension` for `Pest\Expectation` that intercepts methods resolved through the `@mixin Pest\Mixins\Expectation` annotation:

- Type-narrowing methods (`toBeString`, `toBeInt`, etc.) return `Expectation<narrowedType>`
- All other assertion methods preserve the caller's generic type parameter

### 3. TestClosureThisTypeExtension

`FunctionParameterClosureThisExtension` that binds `$this` in closures passed to `it()`, `test()`, `describe()`, `beforeEach()`, `afterEach()`, `beforeAll()`, and `afterAll()`.

The `$this` type is resolved in this order:
1. **Auto-detect**: `PestConfigReader` parses `Pest.php` files to find `uses(X::class)->in(...)` or `pest()->extend(X::class)->in(...)` and maps directories to TestCase classes. The longest-matching directory prefix wins.
2. **Manual fallback**: Falls back to `peststan.testCaseClass` parameter (default: `PHPUnit\Framework\TestCase`).

### 4. PestConfigReader

Parses `Pest.php` configuration files using `nikic/php-parser` with `NameResolver` to resolve fully-qualified class names from `use` statements. Discovers `Pest.php` files automatically from PHPStan's analysis `paths`, or from explicit `peststan.pestConfigFiles`.

### 5. TestCasePropertiesExtension

`PropertiesClassReflectionExtension` that allows dynamic property access on `TestCase` subclasses. Pest supports `$this->prop = value` in `beforeEach()` closures, accessible in test closures. This extension returns `mixed` for any undefined property on a `TestCase` subclass.

Requires `universalObjectCratesClasses` for `PHPUnit\Framework\TestCase` (set automatically in `extension.neon`) because PHPStan only consults `PropertiesClassReflectionExtension` when `allowsDynamicProperties()` returns `true`.

### 6. Configuration (`extension.neon`)

Registers all extensions with PHPStan. Configures `universalObjectCratesClasses` for dynamic property support and `ignoreErrors` for Pest's `@internal` class annotations. Auto-loaded via `phpstan/extension-installer` or manually included.

### 7. TestCallMethodsClassReflectionExtension

`MethodsClassReflectionExtension` that exposes fluent `TestCall` methods coming from Pest mixins and the configured custom `peststan.testCaseClass`.

- Resolves methods from `Pest\Concerns\Testable` and `Pest\Support\HigherOrderCallables`
- Adds public methods from a custom `testCaseClass` when it differs from `PHPUnit\Framework\TestCase`
- Skips native `TestCall` methods so custom helpers do not shadow real API methods

## Testing Approach

Tests use PHPStan's `TypeInferenceTestCase` to verify type assertions. Each test data file uses `assertType()` to declare expected types, and the test runner verifies PHPStan agrees.

Important: The `TestCase` class overrides `getAdditionalConfigFiles()` to load `tests/extension.neon`. This must happen at class definition time (not in `beforeAll`) because PHPStan's `gatherAssertTypes()` creates its container before Pest's `beforeAll` runs.

## Code Quality Tools

- **Rector**: Automated refactoring for PHP 8.2
- **Laravel Pint**: PSR-12 code style
- **PHPStan**: Level max with strict rules
- **Pest**: Test framework
