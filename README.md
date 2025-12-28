# PestStan

PHPStan extension for [Pest PHP](https://pestphp.com/) testing framework.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mrpunyapal/peststan.svg?style=flat-square)](https://packagist.org/packages/mrpunyapal/peststan)
[![Total Downloads on Packagist](https://img.shields.io/packagist/dt/mrpunyapal/peststan.svg?style=flat-square)](https://packagist.org/packages/mrpunyapal/peststan)
[![CI](https://github.com/mrpunyapal/peststan/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/mrpunyapal/peststan/actions/workflows/ci.yml)

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

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

MIT License. See [LICENSE](LICENSE) for more information.

## Credits

- Built for [Pest PHP](https://pestphp.com/)
- Powered by [PHPStan](https://phpstan.org/)
