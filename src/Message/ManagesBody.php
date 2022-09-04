<?php

declare(strict_types=1);

namespace EinarHansen\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;

trait ManagesBody
{
    protected StreamInterface $body;

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(mixed $body = '', $mode = null): static
    {
        /** @var \Psr\Http\Message\StreamInterface $body */
        $body = $this->parseBody(body: $body, mode: $mode);
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }

    /**
     * Adds a json body to the request.
     *
     * @return $this
     */
    public function withJson(array|string $body = ''): static
    {
        return $this
            ->withHeader(
                name: 'Content-Type',
                value: 'application/json'
            )->withBody(
                body: is_array(value: $body)
                    ? json_encode(value: $body)
                    : $body
            );
    }

    /**
     * Adds a form data to the request.
     *
     * @return $this
     */
    public function withForm(array|string $body = ''): static
    {
        return $this
            ->withHeader(
                name: 'Content-Type',
                value: 'application/x-www-form-urlencoded'
            )->withBody(
                body: is_array(value: $body)
                    ? http_build_query(
                        data: $body,
                        numeric_prefix: '',
                        arg_separator: '&'
                    )
                    : $body
            );
    }

    /**
     * Create a Stream object, we expect the following types.
     * - resource
     * - string
     * - \Psr\Http\Message\StreamInterface
     *
     * @return \Psr\Http\Message\StreamInterface
     *
     * @throws InvalidArgumentException If the given URI cannot be parsed.
     */
    public function parseBody(mixed $body = '', string $mode = null): StreamInterface
    {
        if ($body instanceof StreamInterface) {
            return $body;
        }
        if (is_resource(value: $body)) {
            return $this->getStreamFactory()->createStreamFromResource($body);
        }
        if (is_string(value: $body) && ! is_null(value: $mode)) {
            return $this->getStreamFactory()->createStreamFromFile($body, $mode);
        }
        if (is_string(value: $body)) {
            return $this->getStreamFactory()->createStream($body);
        }

        throw new InvalidArgumentException(message: 'The $body is not a valid type. It must be one of:  resource|string|\Psr\Http\Message\StreamInterface');
    }
}
