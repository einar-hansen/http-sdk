<?php

declare(strict_types=1);

namespace EinarHansen\Http\Message;

use InvalidArgumentException;

trait ManagesHeaders
{
    /**
     * The headers that should be sent with each request.
     *
     * @var array<string, string[]>
     */
    private array $headers = [];

    /**
     * Map of lowercase header name => original name at registration
     *
     * @var array<string, string>
     */
    private array $headerNames = [];

    /**
     * @return array<string, string>
     */
    public function getHeaderNames(): array
    {
        return $this->headerNames;
    }

    public function getHeaderName(string $name): ?string
    {
        if (! $this->hasHeader($name)) {
            return null;
        }

        return $this->getHeaderNames()[strtolower($name)];
    }

    public function hasHeader(string $name): bool
    {
        return isset($this->getHeaderNames()[strtolower($name)]);
    }

    /**
     * @return array<string, string[]>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return  array<int, string>
     */
    public function getHeader(string $name): array
    {
        if (! $header = $this->getHeaderName($name)) {
            return [];
        }

        return $this->headers[$header];
    }

    public function getHeaderLine(string $name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    /**
     * Set the headers that should be sent with the request (replace all).
     *
     * @param  array<string, string|string[]>  $headers
     */
    public function withHeaders(array $headers = []): static
    {
        if ($headers === $this->getHeaders()) {
            return $this;
        }

        $clone = clone $this;
        $clone->headerNames = $clone->headers = [];

        foreach ($headers as $name => $value) {
            $clone = $clone->withHeader($name, $value);
        }

        return $clone;
    }

    /**
     * Set or replace the header value for the specified header.
     *
     * @param  string  $name
     * @param  string|int|float|null|array<string, string>  $value
     */
    public function withHeader(string $name, array|string|int|float|null $value): static
    {
        $this->validateHeaderName($name);
        $value = $this->parseHeaderValue($value);

        if ($this->hasHeader($name) && $this->getHeader($name) === $value) {
            return $this;
        }

        $clone = clone $this;
        if ($clone->hasHeader($name)) {
            $clone = $clone->withoutHeader($name);
        }

        $clone->headerNames[strtolower($name)] = $name;
        $clone->headers[$name] = $value;

        return $clone;
    }

    /**
     * Set or add the value to the s the header to the existing headers, but replace the specified header
     *
     * @param  string  $name
     * @param  string|string[]|int|float|null  $value
     */
    public function withAddedHeader(string $name, array|string|int|float|null $value): static
    {
        $this->validateHeaderName($name);
        $value = $this->parseHeaderValue($value);
        $clone = clone $this;

        if ($clone->hasHeader($name)) {
            /** @var string $header */
            $header = $clone->getHeaderName($name);
            $clone->headers[$header] = array_merge($clone->getHeader($header), $value);
        } else {
            $clone->headerNames[strtolower($name)] = $name;
            $clone->headers[$name] = $value;
        }

        return $clone;
    }

    public function withoutHeader(string $name): static
    {
        if (! $this->hasHeader($name)) {
            return $this;
        }

        $clone = clone $this;

        unset(
            $clone->headers[$clone->getHeaderName($name)],
            $clone->headerNames[strtolower($name)]
        );

        return $clone;
    }

    /**
     * From Guzzle Package
     *
     * @param  string|mixed[]|int|float|null  $value
     * @return string[]
     *
     *  @throws InvalidArgumentException for invalid HTTP methods.
     */
    public function parseHeaderValue(array|string|int|float|null $value): array
    {
        if (! is_array($value)) {
            $value = [$value];
        }

        if (count($value) === 0) {
            throw new InvalidArgumentException('Header value can not be an empty array.');
        }

        return array_map(function ($value) {
            // int, float, string or bool
            if (! is_scalar($value) && ! is_null($value)) {
                throw new InvalidArgumentException(sprintf(
                    'Header value must be scalar or null but %s provided.',
                    is_object($value) ? get_class($value) : gettype($value)
                ));
            }

            $trimmed = trim((string) $value, " \t");
            $this->validateHeaderValue($trimmed);

            return $trimmed;
        }, array_values($value));
    }

    /**
     * From Guzzle Package
     *
     * @see https://tools.ietf.org/html/rfc7230#section-3.2
     *
     * @param  string  $name
     */
    private function validateHeaderName(string $name): void
    {
        if (! preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/', $name)) {
            throw new InvalidArgumentException(
                sprintf(
                    '"%s" is not valid header name',
                    $name
                )
            );
        }
    }

    /**
     * From Guzzle Package
     *
     * @param  string|int|float|null  $value
     *
     * @see https://tools.ietf.org/html/rfc7230#section-3.2
     */
    private function validateHeaderValue(string|int|float|bool|null $value): void
    {
        if (! preg_match('/^[\x20\x09\x21-\x7E\x80-\xFF]*$/', (string) $value)) {
            throw new InvalidArgumentException(sprintf('"%s" is not valid header value', $value));
        }
    }
}
