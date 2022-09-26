<?php

declare(strict_types=1);

namespace EinarHansen\Http\Collection;

use EinarHansen\Http\Contracts\Collection\CollectionFactory;
use EinarHansen\Http\Contracts\Data\DataFactory;
use EinarHansen\Http\Support\Arr;
use Exception;
use Psr\Http\Message\ResponseInterface;

class ArrayCollectionFactory implements CollectionFactory
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
    ): iterable {
        return array_map(function ($data) use ($factory, $extraData) {
            if (! is_array($data)) {
                throw new Exception('Collection must consist of array data');
            }

            return $factory->make($data + $extraData);
        }, Arr::transformResponseToCollection($response, $pointer));
    }
}
