<?php

declare(strict_types=1);

namespace EinarHansen\Http\Collection;

use EinarHansen\Http\Factory\FactoryContract;
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
        }, static::transformResponseToArray($response, $pointer));
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * This method is intended to be used as an equal
     * the json machines method.
     *
     * Copied from Laravel: Illuminate\Support\Arr::get()
     *
     * @return  array<int, mixed>
     */
    public static function transformResponseToArray(
        ResponseInterface $response,
        string|int|float|null $key,
        string $delimiter = '/'
    ): array {
        /** @var array<int|string, array<int|string, mixed>> $array */
        $array = json_decode((string) $response->getBody(), true);

        if (! is_array($array)) {
            return [];
        }

        if (is_null($key)) {
            return array_values($array);
        }

        if (is_float($key)) {
            $key = (string) $key;
        }

        if (array_key_exists($key, $array)) {
            return array_values($array[$key]);
        }

        if (! str_contains((string) $key, $delimiter)) {
            return array_values($array[$key] ?? []);
        }

        if (! empty($delimiter)) {
            $segments = explode($delimiter, (string) $key);
            $segments = array_filter($segments, fn (string $value) => $value !== '');
            foreach ($segments as $segment) {
                if (is_array($array) && array_key_exists($segment, $array)) {
                    $array = $array[$segment];
                } else {
                    return [];
                }
            }
        }

        return array_values($array);
    }
}
