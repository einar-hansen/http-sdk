<?php

declare(strict_types=1);

namespace EinarHansen\Http\Support;

use ArgumentCountError;
use BackedEnum;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use InvalidArgumentException;

class AttributeBag
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(
        public array $attributes = []
    ) {
    }

    public function string(string $key, string $default = ''): string
    {
        if (! is_string(value: $value = $this->attribute(key: $key))) {
            return $default;
        }

        return (string) $value;
    }

    public function stringOrNull(string $key): ?string
    {
        return $this->hasAttribute(key: $key) ? $this->string(key: $key) : null;
    }

    public function integer(string $key, int $default = 0): int
    {
        if (! is_numeric(value: $value = $this->attribute(key: $key))) {
            return $default;
        }

        return (int) $value;
    }

    public function integerOrNull(string $key): ?int
    {
        return $this->hasAttribute(key: $key) ? $this->integer(key: $key) : null;
    }

    public function decimal(string $key, float $default = 0.0): float
    {
        $value = $this->hasAttribute(key: $key) ? $this->attribute(key: $key) : $default;
        if (is_float(value: $value)) {
            return $value;
        } elseif (! is_numeric(value: $value)) {
            return $default;
        }

        return (float) str_replace(
            search: ',',
            replace: '.',
            subject: (string) $value
        );
    }

    public function decimalOrNull(string $key): ?float
    {
        return $this->hasAttribute(key: $key) ? $this->decimal(key: $key) : null;
    }

    /**
     * I recommend to always add the format to this method
     *
     * For formats, take a look at
     *
     * @see https://www.php.net/manual/en/class.datetimeinterface.php
     * @see https://www.timestamp-converter.com/
     */
    public function dateTime(
        string $key,
        ?string $format = null,
        DateTimeInterface|string $default = 'now'
    ): DateTimeInterface {
        if (! $this->hasAttribute(key: $key)) {
            return is_string($default) ? new DateTimeImmutable(datetime: $default) : $default;
        }
        if (isset($format)) {
            $date = DateTimeImmutable::createFromFormat(
                format: $format,
                datetime: $this->string(key: $key),
                timezone: null,
            );
        } else {
            if (is_int($attribute = $this->attribute(key: $key))) {
                $date = (new DateTimeImmutable())->setTimestamp(timestamp: $attribute);
            } else {
                $date = new DateTimeImmutable(
                    datetime: $this->string(key: $key),
                );
            }
        }

        if ($date === false) {
            throw new Exception('Failed to create date object from format: '.$format);
        }

        return $date;
    }

    public function date(
        string $key,
        ?string $format = null,
        DateTimeInterface|string $default = 'now'
    ): DateTimeInterface {
        // We copy, in case $default is another DateTimeInterface type without the setTime method.
        $date = DateTimeImmutable::createFromInterface(
            object: $this->dateTime(
                key: $key,
                format: $format,
                default: $default
            ));

        return $date->setTime(
            hour: 0,
            minute: 0,
            second: 0,
            microsecond: 0,
        );
    }

    public function dateOrNull(string $key, ?string $format = null): ?DateTimeInterface
    {
        return $this->hasAttribute(key: $key) ? $this->date(key: $key, format: $format) : null;
    }

    public function dateTimeOrNull(string $key, ?string $format = null): ?DateTimeInterface
    {
        return $this->hasAttribute(key: $key) ? $this->dateTime(key: $key, format: $format) : null;
    }

    /**
     * @return  array<int|string, mixed>
     */
    public function array(string $key): array
    {
        if (! $this->hasAttribute($key)) {
            return [];
        }

        return (array) $this->attribute($key, []);
    }

    /**
     * @return  null|array<int|string, mixed>
     */
    public function arrayOrNull(string $key): ?array
    {
        if (! $this->hasAttribute($key)) {
            return null;
        }

        return (array) $this->attribute($key, []);
    }

    /**
     * @param  string  $key
     * @param  class-string  $enumClass
     * @return BackedEnum
     */
    public function enum(string $key, string $enumClass): BackedEnum
    {
        $this->assertEnumClass($enumClass);

        return $enumClass::from(
            $this->attribute($key, null)
        );
    }

    /**
     * @param  string  $key
     * @param  class-string  $enumClass
     * @return null|BackedEnum
     */
    public function enumOrNull(string $key, string $enumClass): ?BackedEnum
    {
        $this->assertEnumClass($enumClass);

        return $enumClass::tryFrom(
            $this->attribute($key, null)
        );
    }

    /**
     * @param  string  $enumClass
     *
     * @throws \InvalidArgumentException
     */
    protected function assertEnumClass(string $enumClass): void
    {
        if (! class_exists($enumClass)) {
            throw new InvalidArgumentException("The class `$enumClass` does not exists.");
        }

        if (! is_subclass_of($enumClass, BackedEnum::class)) {
            throw new InvalidArgumentException("`$enumClass` must be a subclass of `".BackedEnum::class.'`.');
        }
    }

    /**
     * Retrieve an input item from the request or the current route original parameters.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function attribute(string $key, mixed $default = null)
    {
        if (! $this->hasAttribute($key)) {
            return $default;
        }

        return $this->attributes[$key];
    }

    /**
     * Determine if the request contains a non-empty value for an input or route item.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasAttribute(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    /**
     * @return array<int|string, mixed>
     */
    public function map(string $key, callable $callback): array
    {
        $keys = array_keys(array: $this->array(key: $key));

        try {
            $items = array_map(
                $callback,
                $this->array(key: $key),
                $keys
            );
        } catch (ArgumentCountError) {
            $items = array_map(
                callback: $callback,
                array: $this->array(key: $key),
            );
        }

        return array_combine(keys: $keys, values: $items);
    }

    /**
     * Apply the callback if the given "value" is (or resolves to) truthy.
     */
    public function when(string $key, callable $callback, mixed $default = null): mixed
    {
        if (! $this->hasAttribute(key: $key)) {
            return $default;
        }

        $attribute = $this->attribute(key: $key);
        if (is_null($attribute) || $attribute === false) {
            return $default;
        }

        return $callback($attribute);
    }
}
