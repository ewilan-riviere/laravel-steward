# **steward-laravel**

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kiwilan/steward-laravel.svg?style=flat-square)](https://packagist.org/packages/kiwilan/steward-laravel)
[![npm](https://img.shields.io/npm/v/@kiwilan/vite-plugin-steward-laravel.svg?style=flat-square&color=CB3837&logo=npm&logoColor=ffffff&label=npm)](https://www.npmjs.com/package/@kiwilan/vite-plugin-steward-laravel)
[![codecov](https://codecov.io/gh/kiwilan/steward-laravel/branch/main/graph/badge.svg?token=CBWSPNZSRA)](https://codecov.io/gh/kiwilan/steward-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/kiwilan/steward-laravel.svg?style=flat-square)](https://packagist.org/packages/kiwilan/steward-laravel)

![Run tests](https://github.com/kiwilan/steward-laravel/actions/workflows/run-tests.yml/badge.svg)
![Fix PHP code style issues](https://github.com/kiwilan/steward-laravel/actions/workflows/fix-php-code-style-issues.yml/badge.svg)
[![Netlify Status](https://api.netlify.com/api/v1/badges/849d4a45-1236-4f9e-992c-4a242588aeac/deploy-status)](https://app.netlify.com/sites/steward-laravel/deploys)

PHP package for Laravel to allow you to use some useful traits and methods in your Laravel application, works with [vite-plugin-steward-laravel](https://www.npmjs.com/package/@kiwilan/vite-plugin-steward-laravel) for front assets.

## Documentation

See [steward-laravel.netlify.app](https://steward-laravel.netlify.app/) for documentation.

## Installation

You can install the package via composer:

```bash
composer require kiwilan/steward-laravel:dev-main -W
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="steward-config"
```

### Vite plugin

```bash
npm install --save-dev @kiwilan/vite-plugin-steward-laravel
```

```bash
pnpm add @kiwilan/vite-plugin-steward-laravel -D
```

Check [@kiwilan/vite-plugin-steward-laravel](https://github.com/kiwilan/steward-laravel/tree/main/lib) for usage.

## Testing

```bash
composer test
```

Coverage

```bash
composer test-coverage
```

Watch tests

```bash
composer test:watch
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Ewilan Rivière](https://github.com/ewilan-riviere)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
