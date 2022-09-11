<?php

declare(strict_types=1);

namespace EinarHansen\Http\Support;

use Psr\Http\Message\ResponseInterface;

class Arr
{
    /**
     * Get an item from an array using "dot" notation.
     *
     * This method is intended to be used as an equal
     * the json machines method.
     *
     * Copied from Laravel: Illuminate\Support\Arr::get()
     *
     * @return  array<int|string, mixed>
     */
    public static function transformResponseToArray(
        ResponseInterface $response,
        string|int|float|null $key,
        string $delimiter = '/'
    ): array {
        /** @var array<int|string, array<int|string, mixed>> $array */
        $array = json_decode(
            json: (string) $response->getBody(),
            associative: true
        );
        if (! is_array($array)) {
            return [];
        }

        if (is_null($key)) {
            return $array;
        }

        if (! is_string($key)) {
            $key = (string) $key;
        }

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        if (! str_contains((string) $key, $delimiter)) {
            return $array[$key] ?? [];
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

        return $array;
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
    public static function transformResponseToCollection(
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

        if (! is_string($key)) {
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
