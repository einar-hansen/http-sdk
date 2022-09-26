<?php

declare(strict_types=1);

namespace EinarHansen\Http\Collection;

use EinarHansen\Http\Contracts\Collection\CollectionFactory;
use EinarHansen\Http\Contracts\Data\Data;
use EinarHansen\Http\Contracts\Data\DataFactory;
use EinarHansen\Http\Wrapper\StreamWrapper;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\PassThruDecoder;
use Psr\Http\Message\ResponseInterface;

class LazyCollectionFactory implements CollectionFactory
{
    /**
     * Transform the items of the collection to the given class.
     *
     * @param  array<string, mixed>  $extraData
     * @return  LazyCollection<int, Data>
     */
    public function make(
        ResponseInterface $response,
        DataFactory $factory,
        string $pointer = null,
        array $extraData = []
    ): iterable {
        if (is_numeric(value: $pointer)) {
            $pointer = (string) $pointer;
        }

        if (! is_null(value: $pointer) && substr(string: $pointer, offset: 0, length: 1) !== '/') {
            $pointer = '/'.(string) $pointer;
        }

        $resource = StreamWrapper::getResource(stream: $response->getBody());

        return new LazyCollection(
            items: function () use ($resource, $factory, $pointer, $extraData) {
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
                        /** @var array<string, mixed> $attributes */
                        $attributes = json_decode(json: $data, associative:  true);

                        yield $factory->make($attributes + $extraData);
                    }
                    rewind($resource);
                }
            }
        );
    }
}
