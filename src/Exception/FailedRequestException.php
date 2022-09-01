<?php

declare(strict_types=1);

namespace EinarHansen\Http\Exception;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;

final class FailedRequestException extends Exception implements ClientExceptionInterface
{
}
