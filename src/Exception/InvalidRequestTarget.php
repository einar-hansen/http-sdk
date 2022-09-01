<?php

declare(strict_types=1);

namespace EinarHansen\Http\Exception;

use InvalidArgumentException;
use Psr\Http\Client\ClientExceptionInterface;

final class InvalidRequestTarget extends InvalidArgumentException implements ClientExceptionInterface
{
}
