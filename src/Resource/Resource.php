<?php

declare(strict_types=1);

namespace EinarHansen\Http\Resource;

use EinarHansen\Http\Collection\CollectionFactoryInterface;
use EinarHansen\Http\Exception\InvalidDataFactory;
use EinarHansen\Http\Factory\FactoryContract;
use EinarHansen\Http\Service\ServiceContract;
use EinarHansen\Http\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;

class Resource implements ResourceContract
{
    public function __construct(
        protected readonly ServiceContract $service,
        protected readonly CollectionFactoryInterface $collectionFactory,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function service(): ServiceContract
    {
        return $this->service;
    }

    /**
     * {@inheritDoc}
     */
    public function makeData(
        ResponseInterface $response,
        FactoryContract|string $factory,
        string $pointer = null,
        array $extraData = []
    ): iterable {
        if (is_string(value: $factory)) {
            static::validateFactoryContract(factory: $factory);
            $factory = new $factory();
        }

        $factory->make(
            attributes: Arr::transformResponseToArray(
                response: $response,
                key: $pointer
            ) + $extraData
        );
    }

    /**
     * {@inheritDoc}
     */
    public function makeDataCollection(
        ResponseInterface $response,
        FactoryContract|string $factory,
        string $pointer = null,
        array $extraData = []
    ): iterable {
        if (is_string(value: $factory)) {
            static::validateFactoryContract(factory: $factory);
            $factory = new $factory();
        }

        return $this->collectionFactory
            ->cast(
                response: $response,
                factory: $factory,
                pointer: $pointer,
                extraData: $extraData,
            );
    }

    /**
     * @throws \EinarHansen\Http\Exception\InvalidDataFactory
     */
    public static function validateFactoryContract(string $factory): void
    {
        $reflection = new ReflectionClass(objectOrClass: $factory);
        if (! $reflection->implementsInterface(interface: FactoryContract::class)) {
            throw new InvalidDataFactory('The factory class ['.$factory.'] must be an instance of '.FactoryContract::class);
        }
    }
}
