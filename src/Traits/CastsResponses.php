<?php

declare(strict_types=1);

namespace EinarHansen\Http\Traits;

use EinarHansen\Http\Resource\AbstractResource;
use EinarHansen\Http\Wrapper\StreamWrapper;
use InvalidArgumentException;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\PassThruDecoder;
use Psr\Http\Message\ResponseInterface;

trait CastsResponses
{
    /**
     * Transform the items of the collection to the given class.
     *
     * @param  array<string, mixed>  $extraData
     */
    protected function castToResource(
        ResponseInterface $response,
        string $class,
        string $key = null,
        array $extraData = []
    ): object {
        $this->validateResourceClass($class);
        /** @var array<string, mixed> $attributes */
        $attributes = json_decode((string) $response->getBody(), true);

        if (! is_null($key) && array_key_exists($key, $attributes)) {
            /** @var array<string, mixed> $attributes */
            $attributes = $attributes[$key];
        }

        return new $class($attributes + $extraData, $this);
    }

    /**
     * Transform the items of the collection to the given class.
     *
     * @param   array<string, mixed>  $extraData
     * @return  array<int, mixed>
     */
    protected function castToArray(
        ResponseInterface $response,
        string $class,
        string $pointer = null,
        array $extraData = []
    ): array {
        $this->validateResourceClass($class);

        return array_map(function ($data) use ($class, $extraData) {
            if (! is_array($data)) {
                throw new \Exception('Collection must consist of array data');
            }

            return new $class($data + $extraData, $this);
        }, static::transformResponseToArray($response, $pointer));
    }

    /**
     * Transform the items of the collection to the given class
     * using '/' as pointer.
     *
     * @param   array<string, mixed>  $extraData
     * @return  iterable<int, mixed>
     */
    protected function castToGenerator(
        ResponseInterface $response,
        string $class,
        string $pointer = null,
        array $extraData = []
    ): iterable {
        $this->validateResourceClass($class);
        if (is_numeric($pointer)) {
            $pointer = (string) $pointer;
        }

        if (! is_null($pointer) && substr((string) $pointer, 0, 1) !== '/') {
            $pointer = '/'.(string) $pointer;
        }

        $resource = StreamWrapper::getResource($response->getBody());
        if ($resource !== false) {
            /** @var iterable<string, string> $generator */
            $generator = Items::fromStream(
                $resource,
                [
                    'pointer' => is_null($pointer) ? '' : $pointer,
                    'decoder' => new PassThruDecoder(),
                ]
            );
            foreach ($generator as $key => $data) {
                /** @var array<string|int, mixed> $attributes */
                $attributes = json_decode($data, true);

                yield new $class($attributes + $extraData, $this);
            }
        }
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

    public function validateResourceClass(string $class): void
    {
        if (! is_subclass_of($class, AbstractResource::class)) {
            throw new InvalidArgumentException('The class ['.$class.'] must extend '.AbstractResource::class);
        }
    }
}
