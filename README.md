# Laravel Partial Down

[![Latest Version on Packagist](https://img.shields.io/packagist/v/digifactory/laravel-partial-down.svg?style=flat-square)](https://packagist.org/packages/digifactory/laravel-partial-down)
[![MIT Licensed](https://img.shields.io/github/license/digifactory/laravel-partial-down?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/digifactory/laravel-partial-down/master.svg?style=flat-square)](https://travis-ci.org/digifactory/laravel-partial-down)
[![Quality Score](https://img.shields.io/scrutinizer/g/digifactory/laravel-partial-down.svg?style=flat-square)](https://scrutinizer-ci.com/g/digifactory/laravel-partial-down)
[![StyleCI](https://styleci.io/repos/272963036/shield?branch=master)](https://styleci.io/repos/272963036)
[![Total Downloads](https://img.shields.io/packagist/dt/digifactory/laravel-partial-down.svg?style=flat-square)](https://packagist.org/packages/digifactory/laravel-partial-down)

This package provides a command to put a part of your application's routes in maintenance mode. This only affects your HTTP routes, so queues and scheduled tasks will run.

## Installation

You can install the package via Composer:

```bash
composer require digifactory/partial-down
```

## Usage

You can define the parts you want to put in maintenance mode in your middlewares you use for a route or group:

```php
Route::group(['prefix' => 'backend', 'middleware' => 'partialDown:backend'], function () { });

Route::get('backend', function () { })->middleware('partialDown:backend');
```

Now you can use the artisan commands to put this part of your application in maintenance mode:

```
php artisan partial-down backend
```

And `partial-up` to bring it back online:

```
php artisan partial-down up
```

The `partial-down` command has Laravel's `down` command signature:

```php
protected $signature = 'partial-down {part}
                                     {--message= : The message for the maintenance mode}
                                     {--retry= : The number of seconds after which the request may be retried}
                                     {--allow=* : IP or networks allowed to access the application while in maintenance mode}';
```

When a specific part is down and the IP is not allowed an `MaintenanceModeException` will be thrown, by default Laravel handles this exception with a 503 response. You can customize this, please refer the [Laravel documentation](https://laravel.com/docs/7.x/configuration#maintenance-mode) for more information.

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email helpdesk@digifactory.nl instead of using the issue tracker.

## Credits

- [Mark Jansen](https://github.com/digifactory)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).