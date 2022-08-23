# PHP HTTP Gateway

[![Latest Version on Packagist](https://img.shields.io/packagist/v/einar-hansen/http-gateway.svg)](https://packagist.org/packages/einar-hansen/http-gateway)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.1-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/packagist/l/einar-hansen/http-gateway.svg)](https://packagist.org/packages/einar-hansen/http-gateway)
[![Total Downloads](https://img.shields.io/packagist/dt/einar-hansen/http-gateway.svg)](https://packagist.org/packages/einar-hansen/http-gateway)

A PHP service that allows you to communicate with external apis using PSR-18 clients. This library does not have a dependency on Guzzle or any other library that sends HTTP requests. You can choose what library to use for sending HTTP requests. 

## Installation

This package requires minimum PHP8.1 because of its use of enums.

You can install the package via composer:

```bash
composer require einar-hansen/http-gateway
```

## Getting Started

This package is designed to lets you build packages and sdk's that connects to external API services in record speed. All you have to do is extend the gateway and add your own endpoints and resources.

More documentation is to come...


## Testing
```bash
# Install packages
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php81-composer:latest \
    composer install

# Run code style formatting and static analysis
docker run -it -v $(pwd):/app -w /app php:8.1-alpine vendor/bin/pint src
docker run -it -v $(pwd):/app -w /app php:8.1-alpine vendor/bin/phpstan --level=9 analyse
```

## About
Einar Hansen is a webdeveloper in Oslo, Norway. You'll find more information about me [on my website](https://einarhansen.dev).

## License

The MIT License (MIT).
