<?php

declare(strict_types=1);

namespace EinarHansen\Http\Collection;

use EinarHansen\Http\Factory\FactoryContract;
use Psr\Http\Message\ResponseInterface;

interface CollectionFactoryInterface
{
    /**
     * Transform the items of the collection to the given class.
     *
     * @param  array<string, mixed>  $extraData
     * @return  iterable<int, \EinarHansen\Http\Data\DataContract>
     */
    public static function cast(
        ResponseInterface $response,
        FactoryContract|string $factory,
        string $pointer = null,
        array $extraData = []
    ): iterable;
}
