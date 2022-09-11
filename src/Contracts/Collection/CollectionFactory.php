<?php

declare(strict_types=1);

namespace EinarHansen\Http\Contracts\Collection;

use EinarHansen\Http\Contracts\Data\DataFactory;
use Psr\Http\Message\ResponseInterface;

interface CollectionFactory
{
    /**
     * Transform the items of the collection to the given class.
     *
     * @param  array<string, mixed>  $extraData
     * @return  iterable<int, \EinarHansen\Http\Contracts\Data\Data>
     */
    public function make(
        ResponseInterface $response,
        DataFactory $factory,
        string $pointer = null,
        array $extraData = []
    ): iterable;
}
