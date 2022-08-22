<?php

declare(strict_types=1);

namespace EinarHansen\Http\Message;

use EinarHansen\Http\Enums\RequestMethod;
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

    public function get(): static
    {
        return $this->withMethod(RequestMethod::GET);
    }

    public function head(): static
    {
        return $this->withMethod(RequestMethod::HEAD);
    }

    public function post(): static
    {
        return $this->withMethod(RequestMethod::POST);
    }

    public function put(): static
    {
        return $this->withMethod(RequestMethod::PUT);
    }

    public function delete(): static
    {
        return $this->withMethod(RequestMethod::DELETE);
    }

    public function connect(): static
    {
        return $this->withMethod(RequestMethod::CONNECT);
    }

    public function options(): static
    {
        return $this->withMethod(RequestMethod::OPTIONS);
    }

    public function trace(): static
    {
        return $this->withMethod(RequestMethod::TRACE);
    }

    public function patch(): static
    {
        return $this->withMethod(RequestMethod::PATCH);
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
