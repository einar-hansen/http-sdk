<?php

declare(strict_types=1);

namespace EinarHansen\Http\Message;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

trait ManagesUri
{
    protected UriInterface $uri;

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(UriInterface|string $uri, bool $preserveHost = false): static
    {
        $uri = $this->parseUri($uri);
        if ($uri->__toString() === $this->uri->__toString()) {
            return $this;
        }

        $clone = clone $this;
        $clone->uri = $uri;

        if (! $preserveHost) {
            $clone->updateHostFromUri();
        }

        return $clone;
    }

    public function withScheme(string $scheme): static
    {
        $uri = $this->getUri();
        $clone = $this->withUri(
            uri: $uri->withScheme($scheme),
            preserveHost: false
        );

        return $clone;
    }

    public function withHost(string $host): static
    {
        $uri = $this->getUri();
        $clone = $this->withUri(
            uri: $uri->withHost($host),
            preserveHost: false
        );

        return $clone;
    }

    public function withPort(?int $port = null): static
    {
        $uri = $this->getUri();
        $clone = $this->withUri(
            uri: $uri->withPort($port),
            preserveHost: false
        );

        return $clone;
    }

    public function withBaseUri(UriInterface|string $uri): static
    {
        $uri = $this->parseUri($uri);

        return $this->withUri(
            uri: $this->getUri()
                ->withScheme($uri->getScheme())
                ->withHost($uri->getHost())
                ->withPort($uri->getPort()),
            preserveHost: false
        );
    }

    public function withRelativeUri(UriInterface|string $uri): static
    {
        $uri = $this->parseUri($uri);

        return $this->withUri(
            uri: $this->getUri()
                ->withPath($uri->getPath())
                ->withQuery($uri->getQuery())
                ->withFragment($uri->getFragment()),
            preserveHost: false
        );
    }

    public function withPath(string $path): static
    {
        $uri = $this->getUri();
        $clone = $this->withUri(
            uri: $uri->withPath($path),
            preserveHost: true
        );

        return $clone;
    }

    /**
     * Replaces the whole query and return a cloned object.
     *
     * @param  string|array<string, string|string[]>  $query
     */
    public function withQuery(array|string $query): static
    {
        $uri = $this->getUri();
        if (is_array($query)) {
            $query = http_build_query($query, '', '&', \PHP_QUERY_RFC3986);
        }
        $clone = $this->withUri(
            uri: $uri->withQuery(
                query: $query
            ),
            preserveHost: true
        );

        return $clone;
    }

    /**
     * Creates a new cloned object with the specific query string value removed.
     *
     * Any existing query string values that exactly match the provided key are
     * removed.
     */
    public function withoutQueryValue(string $key): static
    {
        $result = self::getFilteredQueryString(
            uri: $this->getUri(),
            keys: [$key]
        );

        return $this->withQuery(implode('&', $result));
    }

    /**
     * Creates a new cloned object with a specific query string value.
     *
     * Any existing query string values that exactly match the provided key are
     * removed and replaced with the given key value pair.
     *
     * A value of null will set the query string key without a value, e.g. "key"
     * instead of "key=value".
     *
     * @param  string  $key   Key to set.
     * @param  string|null  $value Value to set
     */
    public function withQueryValue(string $key, string|int|float $value = null): static
    {
        $result = self::getFilteredQueryString($this->getUri(), [$key]);

        $result[] = self::generateQueryString($key, $value);

        return $this->withQuery(implode('&', $result));
    }

    /**
     * Creates a new URI with multiple specific query string values.
     *
     * @param  array<string, string|string[]|null>  $array
     */
    public function withQueryValues(array $array = []): static
    {
        $result = self::getFilteredQueryString($this->getUri(), array_keys($array));

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = implode(
                    separator: ',',
                    array: $value
                );
            }
            $result[] = self::generateQueryString((string) $key, $value !== null ? (string) $value : null);
        }

        return $this->withQuery(implode('&', $result));
    }

    public function withFragment(string $fragment): static
    {
        $uri = $this->getUri();
        $clone = $this->withUri(
            uri: $uri->withFragment($fragment),
            preserveHost: true
        );

        return $clone;
    }

    /**
     * Based on GuzzleHttp\Psr7\Request::updateHostFromUri()
     */
    private function updateHostFromUri(): void
    {
        $host = $this->getUri()->getHost();

        if ($host == '') {
            return;
        }

        if (($port = $this->getUri()->getPort()) !== null) {
            $host .= ':'.$port;
        }

        // Ensure Host is the first header.
        // See: http://tools.ietf.org/html/rfc7230#section-5.4
        $this->headerNames = ['host' => 'Host'] + $this->headerNames;
        $this->headers = ['Host' => [$host]] + $this->headers;
    }

    /**
     * Create a new URI.
     *
     * @param  string  $uri
     * @return UriInterface
     *
     * @throws InvalidArgumentException If the given URI cannot be parsed.
     */
    public function parseUri(UriInterface|string $uri = ''): UriInterface
    {
        if ($uri instanceof UriInterface) {
            return $uri;
        }

        return $this->getUriFactory()->createUri($uri);
    }

    /**
     * Based on GuzzleHttp\Psr7\Request::getFilteredQueryString()
     *
     * @param  string[]  $keys
     * @return string[]
     */
    private static function getFilteredQueryString(UriInterface $uri, array $keys): array
    {
        $current = $uri->getQuery();

        if ($current === '') {
            return [];
        }

        $decodedKeys = array_map('rawurldecode', $keys);

        return array_filter(explode('&', $current), function ($part) use ($decodedKeys) {
            return ! in_array(rawurldecode(explode('=', $part)[0]), $decodedKeys, true);
        });
    }

    private static function generateQueryString(string $key, string|int|float $value = null): string
    {
        // Query string separators ("=", "&") within the key or value need to be encoded
        // (while preventing double-encoding) before setting the query string. All other
        // chars that need percent-encoding will be encoded by withQuery().
        $querySeparatorsReplacement = ['=' => '%3D', '&' => '%26'];

        $queryString = strtr($key, $querySeparatorsReplacement);

        if ($value !== null) {
            $queryString .= '='.strtr((string) $value, $querySeparatorsReplacement);
        }

        return $queryString;
    }
}
