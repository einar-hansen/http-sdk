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

    public function withBody(mixed $body = ''): static
    {
        /** @var \Psr\Http\Message\StreamInterface $body */
        $body = $this->parseBody($body);
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
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
        if (is_resource($body)) {
            return $this->getStreamFactory()->createStreamFromResource($body);
        }
        if (is_string($body) && ! is_null($mode)) {
            return $this->getStreamFactory()->createStreamFromFile($body, $mode);
        }
        if (is_string($body)) {
            return $this->getStreamFactory()->createStream($body);
        }

        throw new InvalidArgumentException('The $body is not a valid type. It must be one of:  resource|string|\Psr\Http\Message\StreamInterface');
    }
}
