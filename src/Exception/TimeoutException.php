<?php

declare(strict_types=1);

namespace EinarHansen\Http\Exception;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;

final class TimeoutException extends Exception implements ClientExceptionInterface
{
    /**
     * Create a new exception instance.
     *
     * @param  array<mixed>  $output
     * @return void
     */
    public function __construct(public array $output = [])
    {
        parent::__construct('Script timed out while waiting for the process to complete.');

        $this->output = $output;
    }

    /**
     * The output returned from the operation.
     *
     * @return array<mixed>
     */
    public function output(): array
    {
        return $this->output;
    }
}
