<?php

declare(strict_types=1);

namespace EinarHansen\Http\Contracts\Resource;

use EinarHansen\Http\Contracts\Data\Data;
use EinarHansen\Http\Contracts\Data\DataFactory;
use EinarHansen\Http\Contracts\Service\Service;
use Psr\Http\Message\ResponseInterface;

interface Resource
{
    /**
     * Retrieve the built Service from the Resource.
     *
     * @return \EinarHansen\Http\Contracts\Service\Service
     */
    public function service(): Service;

    /**
     * Transform the the response into a data object of the given class.
     *
     * @param  DataFactory|class-string  $factory
     * @param  array<string, mixed>  $extraData
     *
     * @throws \EinarHansen\Http\Exception\InvalidDataFactory
     */
    public function makeData(
        ResponseInterface $response,
        DataFactory|string $factory,
        string $pointer = null,
        array $extraData = []
    ): Data;

    /**
     * Transform the items in the response to a collection of the the given class.
     *
     * @param  DataFactory|class-string  $factory
     * @param  array<string, mixed>  $extraData
     * @return  iterable<int, \EinarHansen\Http\Contracts\Data\Data>
     *
     * @throws \EinarHansen\Http\Exception\InvalidDataFactory
     */
    public function makeDataCollection(
        ResponseInterface $response,
        DataFactory|string $factory,
        string $pointer = null,
        array $extraData = []
    ): iterable;
}
