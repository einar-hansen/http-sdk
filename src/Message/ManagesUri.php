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
        $clone = $this->withUri($uri->withScheme($scheme), false);

        return $clone;
    }

    public function withHost(string $host): static
    {
        $uri = $this->getUri();
        $clone = $this->withUri($uri->withHost($host), false);

        return $clone;
    }

    public function withPort(?int $port = null): static
    {
        $uri = $this->getUri();
        $clone = $this->withUri($uri->withPort($port), false);

        return $clone;
    }

    public function withBaseUri(UriInterface|string $uri): static
    {
        $uri = $this->parseUri($uri);

        return $this->withUri(
            $this->getUri()
                ->withScheme($uri->getScheme())
                ->withHost($uri->getHost())
                ->withPort($uri->getPort()),
            false
        );
    }

    public function withRelativeUri(UriInterface|string $uri): static
    {
        $uri = $this->parseUri($uri);

        return $this->withUri(
            $this->getUri()
                ->withPath($uri->getPath())
                ->withQuery($uri->getQuery())
                ->withFragment($uri->getFragment()),
            false
        );
    }

    public function withPath(string $path): static
    {
        $uri = $this->getUri();
        $clone = $this->withUri($uri->withPath($path), true);

        return $clone;
    }

    public function withQuery(array|string $query): static
    {
        $uri = $this->getUri();
        if (is_array($query)) {
            $query = http_build_query($query, '', '&', \PHP_QUERY_RFC3986);
        }
        $clone = $this->withUri($uri->withQuery($query), true);

        return $clone;
    }

    public function withFragment(string $fragment): static
    {
        $uri = $this->getUri();
        $clone = $this->withUri($uri->withFragment($fragment), true);

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
}
