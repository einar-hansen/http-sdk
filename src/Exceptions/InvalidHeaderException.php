<?php

declare(strict_types=1);

namespace EinarHansen\Http\Exceptions;

use InvalidArgumentException;
use Psr\Http\Client\ClientExceptionInterface;

final class InvalidHeaderException extends InvalidArgumentException implements ClientExceptionInterface
{
}
