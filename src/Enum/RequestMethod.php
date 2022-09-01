<?php

declare(strict_types=1);

namespace EinarHansen\Http\Enum;

/**
 * To comply with Psr\Http\Message\RequestInterface::class methods
 * "setMethod" and "getMethod" we need to use backed enums, as
 * the methods uses string values.
 */
enum RequestMethod: string
{
    case GET = 'GET';
    case HEAD = 'HEAD';
    case POST = 'POST';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
    case CONNECT = 'CONNECT';
    case OPTIONS = 'OPTIONS';
    case TRACE = 'TRACE';
    case PATCH = 'PATCH';
}
