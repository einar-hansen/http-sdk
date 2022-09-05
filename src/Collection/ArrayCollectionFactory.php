<?php

declare(strict_types=1);

namespace EinarHansen\Http\Collection;

use EinarHansen\Http\Factory\FactoryContract;
use EinarHansen\Http\Support\Arr;
use Exception;
use Psr\Http\Message\ResponseInterface;

class ArrayCollectionFactory implements CollectionFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function make(
        ResponseInterface $response,
        FactoryContract $factory,
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
