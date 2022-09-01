<?php

declare(strict_types=1);

namespace EinarHansen\Http\Message;

use EinarHansen\Http\Enum\RequestMethod;
use InvalidArgumentException;

trait ManagesMethod
{
    protected RequestMethod $method = RequestMethod::GET;

    public function getMethod(): RequestMethod
    {
        return $this->method;
    }

    /**
     * Sets the request method for the request.
     *
     * @throws InvalidArgumentException for invalid HTTP methods.
     */
    public function withMethod(RequestMethod|string $method): static
    {
        $method = $this->parseMethod($method);
        if ($method === $this->getMethod()) {
            return $this;
        }

        $clone = clone $this;
        $clone->method = $method;

        return $clone;
    }

    /**
     * @throws InvalidArgumentException for invalid HTTP methods.
     */
    public function parseMethod(RequestMethod|string $method): RequestMethod
    {
        if ($method instanceof RequestMethod) {
            return $method;
        }

        if ($enum = RequestMethod::tryFrom(strtoupper($method))) {
            return $enum;
        }

        throw new InvalidArgumentException("The '{$method}' is not a valid request method.");
    }
}
