# Development Guide

## Installation

```bash
composer install
```

## Development Commands

### Run All Tests
```bash
composer test
```

This command runs:
1. Code quality checks (Rector)
2. Code style checks (Laravel Pint)
3. Static analysis (PHPStan)
4. Unit tests (Pest)

### Individual Commands

#### Linting & Code Quality
```bash
# Run Rector (refactoring)
composer lint

# Check without applying changes
composer test:lint
```

#### Code Formatting
```bash
# Format code with Laravel Pint
vendor/bin/pint

# Check formatting without applying
vendor/bin/pint --test
```

#### Static Analysis
```bash
# Run PHPStan
composer test:types
```

#### Unit Tests
```bash
# Run Pest tests
composer test:unit
```

## Project Structure

```
PestStan/
├── src/
│   └── Type/
│       └── Pest/
│           ├── ExpectFunctionDynamicReturnTypeExtension.php
│           ├── ExpectationTypeSpecifyingExtension.php
│           └── TestClosureThisTypeExtension.php
├── stubs/
│   └── Pest.stub
├── tests/
│   ├── Pest.php
│   ├── TestCase.php
│   └── Type/
│       └── ExpectTypeTest.php
├── extension.neon
├── composer.json
├── phpstan.neon.dist
├── rector.php
└── pint.json
```

## How It Works

### 1. Function Stubs
[stubs/Pest.stub](stubs/Pest.stub) provides type information for Pest's global functions and the `Expectation` class.

### 2. Type Extensions

- **ExpectFunctionDynamicReturnTypeExtension**: Makes `expect($value)` return `Expectation<typeof $value>`
- **TestClosureThisTypeExtension**: Binds `$this` in test closures to `TestCase`
- **ExpectationTypeSpecifyingExtension**: Provides type narrowing for expectation methods (future enhancement)

### 3. Configuration
[extension.neon](extension.neon) registers all type extensions with PHPStan.

## Testing

The package includes basic integration tests that verify:
- Pest functions are available
- `$this` is properly bound to `TestCase` in test closures
- `expect()` function returns the correct type

## Contributing

1. Make your changes
2. Run `composer lint` to apply code quality improvements
3. Run `composer test` to ensure everything passes
4. Submit a pull request

## Code Quality Tools

- **Rector**: Automated refactoring and code quality improvements
- **Laravel Pint**: Code style fixer based on PSR-12
- **PHPStan**: Static analysis at maximum level with strict rules
- **Pest**: Modern testing framework
