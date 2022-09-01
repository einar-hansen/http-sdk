<?php

declare(strict_types=1);

namespace EinarHansen\Http\Exception;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;

final class NotFoundException extends Exception implements ClientExceptionInterface
{
    /**
     * Create a new exception instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct('The resource you are looking for could not be found.');
    }
}
