<?php

declare(strict_types=1);

namespace EinarHansen\Http\Message;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class RequestFactory implements RequestFactoryInterface
{
    use Conditionable;
    use ManagesRequestFactory;
    use ManagesUriFactory;
    use ManagesStreamFactory;
    use ManagesMethod;
    use ManagesUri;
    use ManagesHeaders;
    use ManagesBody;

    public function __construct(
        RequestFactoryInterface $requestFactory = null,
        UriFactoryInterface $uriFactory = null,
        StreamFactoryInterface $streamFactory = null,
    ) {
        $this->setRequestFactory($requestFactory);
        $this->setUriFactory($uriFactory);
        $this->setStreamFactory($streamFactory);
        $this->uri = $this->parseUri('https://localhost/');
        $this->body = $this->parseBody('');
    }

    public function create(): RequestInterface
    {
        return $this->createRequest($this->getMethod()->value, $this->getUri());
    }

    /**
     * {@inheritDoc}
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        $request = $this->getRequestFactory()
            ->createRequest(
                $this->parseMethod($method)->value,
                $this->parseUri($uri)
            );

        foreach ($this->getHeaders() as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        return $request->withBody($this->getBody());
    }

    /**
     * Applies the array of request options to a request.
     *
     * Based on GuzzleHttp\Client::applyOptions()
     *
     * @param  array<string, mixed>  $options
     */
    public function withOptions(array $options = []): static
    {
        $clone = clone $this;

        if (isset($options['headers']) && is_array($options['headers'])) {
            foreach ($options['headers'] as $name => $value) {
                $clone = $clone->withHeader($name, $value);
            }
        }

        if (isset($options['form_params'])) {
            $clone = $clone->withBody($options['body'])
                ->withHeader('Content-Type', 'application/x-www-form-urlencoded');
        }

        if (isset($options['multipart'])) {
            $clone = $clone->withBody($options['multipart']);
        }
        if (isset($options['body'])) {
            $clone = $clone->withBody($options['body']);
        }

        if (isset($options['json'])) {
            $clone = $clone->withBody(json_encode($options['json']))
                ->withHeader('Content-Type', 'application/json');
        }

        if (isset($options['query'])) {
            /** @var array<string, string|string[]> $query */
            $query = $options['query'];
            $clone = $clone->withQuery($query);
        }

        return $clone;
    }
}
