<?php

declare(strict_types=1);

namespace EinarHansen\Http\Resource;

use EinarHansen\Http\Data\DataContract;
use EinarHansen\Http\Factory\FactoryContract;
use EinarHansen\Http\Service\ServiceContract;
use Psr\Http\Message\ResponseInterface;

interface ResourceContract
{
    /**
     * Retrieve the built Service from the Resource.
     *
     * @return \EinarHansen\Http\Service\ServiceContract
     */
    public function service(): ServiceContract;

    /**
     * Transform the the response into a data object of the given class.
     *
     * @throws \EinarHansen\Http\Exception\InvalidDataFactory
     */
    public function makeData(
        ResponseInterface $response,
        FactoryContract|string $factory,
        string $pointer = null,
        array $extraData = []
    ): DataContract;

    /**
     * Transform the items in the response to a collection of the the given class.
     *
     * @param  array<string, mixed>  $extraData
     * @return  iterable<int, \EinarHansen\Http\Data\DataContract>
     *
     * @throws \EinarHansen\Http\Exception\InvalidDataFactory
     */
    public function makeDataCollection(
        ResponseInterface $response,
        FactoryContract|string $factory,
        string $pointer = null,
        array $extraData = []
    ): iterable;
}
