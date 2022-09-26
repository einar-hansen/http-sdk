# PHP HTTP Gateway

[![Latest Version on Packagist](https://img.shields.io/packagist/v/einar-hansen/http-sdk.svg)](https://packagist.org/packages/einar-hansen/http-sdk)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.1-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/packagist/l/einar-hansen/http-sdk.svg)](https://packagist.org/packages/einar-hansen/http-sdk)
[![Total Downloads](https://img.shields.io/packagist/dt/einar-hansen/http-sdk.svg)](https://packagist.org/packages/einar-hansen/http-sdk)

A PHP service that allows you to communicate with external apis using PSR-18 clients. This library does not have a dependency on Guzzle or any other library that sends HTTP requests. You can choose what library to use for sending HTTP requests. 

## Installation

This package requires minimum PHP8.1 because of its use of enums.

You can install the package via composer:

```bash
composer require einar-hansen/http-sdk
```

## Getting Started

This package is designed to lets you build packages and sdk's that connects to external API services in record speed. All you have to do is extend the gateway and add your own endpoints and resources.

## Implementations

- [PHP FootballData - API Service](https://github.com/einar-hansen/php-football-data)

## Testing

This package requires PHP8.1. If you don't have this version locally or as default PHP version, then you can use the `bin/develop` helper script. The script is inspired by Laravel Sail, but is much simpler. To use the script you should have Docker installed. It will pull down PHP8.1 for you and allow you to run the testing commands below.

To use the script
```bash
# Enable helper script
chmod +x bin/develop

# Install PHP dependencies
bin/develop composer install

# Run code style formatting
bin/develop format

# Run static analysis
bin/develop analyse

# Run tests
bin/develop test
```

## About
Einar Hansen is a webdeveloper in Oslo, Norway. You'll find more information about me [on my website](https://einarhansen.dev).

## License

The MIT License (MIT).

