<?php

declare(strict_types=1);

namespace EinarHansen\Http\Resource;

use EinarHansen\Http\Gateway\Gateway;
use ReflectionObject;
use ReflectionProperty;

abstract class AbstractResource
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public array $attributes = [],
        protected ?Gateway $gateway = null
    ) {
        $this->fill();
    }

    protected function fill(): void
    {
        foreach ($this->attributes as $key => $value) {
            $key = $this->camelCase($key);

            $this->{$key} = $value;
        }
    }

    protected function camelCase(string $key): string
    {
        $parts = explode('_', $key);

        foreach ($parts as $i => $part) {
            if ($i !== 0) {
                $parts[$i] = ucfirst($part);
            }
        }

        return str_replace(' ', '', implode(' ', $parts));
    }

    /**
     * Transform the items of the collection to the given class.
     *
     * @param   array<int, array<string, string>>  $collection
     * @param   string  $class
     * @param   array<string, string>  $extraData
     * @return  array<int, mixed>
     */
    protected function castToArray(
        array $collection,
        string $class,
        array $extraData = []
    ): array {
        return array_map(function ($data) use ($class, $extraData) {
            return new $class($data + $extraData, $this->gateway);
        }, $collection);
    }

    public function __sleep()
    {
        $publicProperties = (new ReflectionObject($this))->getProperties(ReflectionProperty::IS_PUBLIC);

        $publicPropertyNames = array_map(function (ReflectionProperty $property) {
            return $property->getName();
        }, $publicProperties);

        return array_diff($publicPropertyNames, ['gateway', 'attributes']);
    }
}
