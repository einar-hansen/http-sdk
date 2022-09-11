<?php

declare(strict_types=1);

namespace EinarHansen\Http\Resource;

use EinarHansen\Http\Collection\ArrayCollectionFactory;
use EinarHansen\Http\Contracts\Collection\CollectionFactory;
use EinarHansen\Http\Contracts\Data\Data;
use EinarHansen\Http\Contracts\Data\DataFactory;
use EinarHansen\Http\Contracts\Resource\Resource as ResourceContract;
use EinarHansen\Http\Contracts\Service\Service;
use EinarHansen\Http\Exception\InvalidDataFactory;
use EinarHansen\Http\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;

class Resource implements ResourceContract
{
    protected readonly CollectionFactory $collectionFactory;

    public function __construct(
        protected readonly Service $service,
        CollectionFactory $collectionFactory = null,
    ) {
        $this->collectionFactory = $collectionFactory ?? new ArrayCollectionFactory();
    }

    /**
     * {@inheritDoc}
     */
    public function service(): Service
    {
        return $this->service;
    }

    /**
     * {@inheritDoc}
     */
    public function makeData(
        ResponseInterface $response,
        DataFactory|string $factory,
        string $pointer = null,
        array $extraData = []
    ): Data {
        if (is_string(value: $factory)) {
            static::validateFactoryContract(factory: $factory);
            /** @var \EinarHansen\Http\Contracts\Data\DataFactory $factory */
            $factory = new $factory();
        }

        return $factory->make(
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
        DataFactory|string $factory,
        string $pointer = null,
        array $extraData = []
    ): iterable {
        if (is_string(value: $factory)) {
            static::validateFactoryContract(factory: $factory);
            /** @var \EinarHansen\Http\Contracts\Data\DataFactory $factory */
            $factory = new $factory();
        }

        return $this->collectionFactory
            ->make(
                response: $response,
                factory: $factory,
                pointer: $pointer,
                extraData: $extraData,
            );
    }

    /**
     * @param  class-string  $factory
     *
     * @throws \EinarHansen\Http\Exception\InvalidDataFactory
     */
    public static function validateFactoryContract(string $factory): void
    {
        if (! class_exists($factory)) {
            throw new InvalidDataFactory('The factory class ['.$factory.'] provided does not exists!');
        }
        $reflection = new ReflectionClass($factory);
        if (! $reflection->implementsInterface(interface: DataFactory::class)) {
            throw new InvalidDataFactory('The factory class ['.$factory.'] must be an instance of '.DataFactory::class);
        }
    }
}
