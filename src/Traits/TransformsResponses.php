<?php

declare(strict_types=1);

namespace EinarHansen\Http\Traits;

use EinarHansen\Http\AbstractResource;
use GuzzleHttp\Psr7\StreamWrapper;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\PassThruDecoder;
use Psr\Http\Message\ResponseInterface;

trait TransformsResponses
{
    /**
     * Transform the items of the collection to the given class.
     */
    protected function transformResource(
        ResponseInterface $response,
        string $class,
        string $pointer = null,
        array $extraData = []
    ): AbstractResource {
        return new $class(json_decode((string) $response->getBody(), true) + $extraData, $this);
    }

    /**
     * Transform the items of the collection to the given class.
     */
    protected function transformCollectionToArray(
        ResponseInterface $response,
        string $class,
        string $pointer = null,
        array $extraData = []
    ): array {
        return array_map(function ($data) use ($class, $extraData) {
            return new $class($data + $extraData, $this);
        }, static::transformResponseToArray($response, $pointer));
    }

    /**
     * Transform the items of the collection to the given class
     * using '/' as pointer.
     */
    protected function transformCollectionToGenerator(
        ResponseInterface $response,
        string $class,
        string $pointer = null,
        array $extraData = []
    ): iterable {
        if (is_numeric($pointer)) {
            $pointer = (string) $pointer;
        }

        if (! is_null($pointer) && substr((string) $pointer, 0, 1) !== '/') {
            $pointer = '/'.(string) $pointer;
        }

        $generator = Items::fromStream(
            StreamWrapper::getResource($response->getBody()),
            [
                'pointer' => is_null($pointer) ? '' : $pointer,
                'decoder' => new PassThruDecoder(),
            ]
        );
        foreach ($generator as $key => $data) {
            yield new $class(json_decode($data, true) + $extraData, $this);
        }
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * Copied from Laravel: Illuminate\Support\Arr::get()
     */
    public static function transformResponseToArray(
        ResponseInterface $response,
        string|int|null $key,
        array $default = []
    ): array {
        $array = json_decode((string) $response->getBody(), true);

        if (! is_array($array)) {
            return $default;
        }

        if (is_null($key)) {
            return $array;
        }

        if (is_float($key)) {
            $key = (string) $key;
        }

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        if (! str_contains($key, '/')) {
            return $array[$key] ?? $default;
        }

        $segments = explode('/', $key);
        $segments = array_filter($segments, fn (string $value) => $value !== '');
        foreach ($segments as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }
}
