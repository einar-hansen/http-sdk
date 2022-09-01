<?php

declare(strict_types=1);

namespace EinarHansen\Http\Message;

use EinarHansen\Http\Trait\Conditionable;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class RequestFactory implements RequestFactoryInterface
{
    use Conditionable;
    use ManagesClient;
    use ManagesRequestFactory;
    use ManagesUriFactory;
    use ManagesStreamFactory;
    use ManagesMethod;
    use ManagesUri;
    use ManagesHeaders;
    use ManagesBody;

    public function __construct(
        ClientInterface $client = null,
        RequestFactoryInterface $requestFactory = null,
        StreamFactoryInterface $streamFactory = null,
        UriFactoryInterface $uriFactory = null,
    ) {
        $this->setClient($client);
        $this->setRequestFactory($requestFactory);
        $this->setStreamFactory($streamFactory);
        $this->setUriFactory($uriFactory);
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
}
