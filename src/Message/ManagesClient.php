<?php

declare(strict_types=1);

namespace EinarHansen\Http\Message;

use EinarHansen\Http\Enum\RequestMethod;
use Exception;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;

trait ManagesClient
{
    protected ClientInterface $client;

    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    public function setClient(ClientInterface $client = null): self
    {
        $this->client = $client ?: Psr18ClientDiscovery::find();

        return $this;
    }

    public function withClient(RequestFactoryInterface $requestFactory = null): static
    {
        $clone = clone $this;
        $clone->setRequestFactory(requestFactory: $client);

        return $clone;
    }

    public function send(): ResponseInterface
    {
        return $this->client->sendRequest($this->create());
    }

    /**
     * Make a GET request to the service and return the response.
     *
     * @param  string|null|array<string, string|string[]>  $query
     */
    public function get(string $url, array $query = []): ResponseInterface
    {
        return $this->withMethod(method: RequestMethod::GET)
            ->withRelativeUri(uri: $url)
            ->withQueryValues(array: $query)
            ->send();
    }

    /**
     * Make a GET request to the service and return the response.
     */
    public function head(string $url, $query = []): ResponseInterface
    {
        return $this->withMethod(method: RequestMethod::HEAD)
            ->withRelativeUri(uri: $url)
            ->withQueryValues(array: $query)
            ->send();
    }

    /**
     * Make a POST request to the service and return the response.
     *
     * @param  array<string, mixed>  $options
     */
    public function post(string $url, array $body = []): ResponseInterface
    {
        // To be completed;
        throw new Exception('Method not implemented yet');

        return $this->withMethod(method: RequestMethod::POST)
            ->withRelativeUri(uri: $url)
            ->withBody(body: $body)
            ->send();
    }

    /**
     * Make a PUT request to the service and return the response.
     *
     * @param  array<string, mixed>  $options
     */
    public function put(string $url, array $body = []): ResponseInterface
    {
        // To be completed;
        throw new Exception('Method not implemented yet');

        return $this->withMethod(method: RequestMethod::PUT)
            ->withRelativeUri(uri: $url)
            ->withBody(body: $body)
            ->send();
    }

    /**
     * Make a PUT request to the service and return the response.
     *
     * @param  array<string, mixed>  $options
     */
    public function patch(string $url, array $body = []): ResponseInterface
    {
        // To be completed;
        throw new Exception('Method not implemented yet');

        return $this->withMethod(method: RequestMethod::PATCH)
            ->withRelativeUri(uri: $url)
            ->withBody(body: $body)
            ->send();
    }

    /**
     * Make a DELETE request to the service and return the response.
     *
     * @param  array<string, mixed>  $options
     */
    public function delete(string $url, array $body = []): ResponseInterface
    {
        // To be completed;
        throw new Exception('Method not implemented yet');

        return $this->withMethod(method: RequestMethod::DELETE)
            ->withRelativeUri(uri: $url)
            ->withBody(body: $body)
            ->send();
    }

    public function connect(string $url): static
    {
        return $this->withMethod(method: RequestMethod::CONNECT)
            ->withRelativeUri(uri: $url)
            ->send();
    }

    public function options(string $url): static
    {
        return $this->withMethod(method: RequestMethod::OPTIONS)
            ->withRelativeUri(uri: $url)
            ->send();
    }

    public function trace(string $url): static
    {
        return $this->withMethod(method: RequestMethod::TRACE)
            ->withRelativeUri(uri: $url)
            ->send();
    }

    /**
     * Retry the callback or fail after x seconds.
     *
     * @throws \EinarHansen\Http\Exception\TimeoutException
     */
    public function retry(int $timeout, callable $callback, int $sleep = 5): mixed
    {
        $start = time();

        beginning:

        if ($output = $callback()) {
            return $output;
        }

        if (time() - $start < $timeout) {
            sleep($sleep);

            goto beginning;
        }

        if ($output === null || $output === false) {
            $output = [];
        }

        if (! is_array($output)) {
            $output = [$output];
        }

        throw new TimeoutException($output);
    }
}
