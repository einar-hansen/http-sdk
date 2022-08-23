<?php

declare(strict_types=1);

namespace EinarHansen\Http\Gateway;

use EinarHansen\Http\Client\HttpClient;
use EinarHansen\Http\Enums\RequestMethod;
use EinarHansen\Http\Exceptions\NotFoundException;
use EinarHansen\Http\Exceptions\TimeoutException;
use EinarHansen\Http\Exceptions\ValidationException;
use EinarHansen\Http\Message\RequestFactory;
use EinarHansen\Http\Traits\CastsResponses;
use Exception;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

/**
 * This Gateway is what is called a "Calling Library" in PSR-18.
 */
class Gateway
{
    use CastsResponses;

    protected mixed $options = null;

    protected HttpClient $client;

    protected RequestFactory $requestFactory;

    public function __construct(
        mixed $options = null,
        ClientInterface $client = null,
        RequestFactoryInterface $requestFactory = null,
        UriFactoryInterface $uriFactory = null,
        StreamFactoryInterface $streamFactory = null,
    ) {
        $this->options = $options;
        $this->client = $this->createHttpClient($client);
        $this->requestFactory = $this->createRequestFactory(
            $requestFactory,
            $uriFactory,
            $streamFactory
        );
        if (method_exists($this, 'setUp')) {
            $this->setUp($options);
        }
    }

    public function tapHttpClient(callable $callback): self
    {
        $this->client = $callback($this->client);

        return $this;
    }

    public function tapRequestFactory(callable $callback): self
    {
        $this->requestFactory = $callback($this->requestFactory);

        return $this;
    }

    /**
     * @throws \Http\Discovery\Exception\NotFoundException If no PSR-18 clients found.
     */
    public static function createHttpClient(ClientInterface $client = null): HttpClient
    {
        return new HttpClient($client);
    }

    /**
     * @throws \Http\Discovery\Exception\NotFoundException If no PSR-17 factories are found..
     */
    public static function createRequestFactory(
        RequestFactoryInterface $requestFactory = null,
        UriFactoryInterface $uriFactory = null,
        StreamFactoryInterface $streamFactory = null,
    ): RequestFactory {
        return new RequestFactory(
            $requestFactory,
            $uriFactory,
            $streamFactory
        );
    }

    public function getHttpClient(): HttpClient
    {
        return $this->client;
    }

    public function getRequestFactory(): RequestFactory
    {
        return $this->requestFactory;
    }

    /**
     * @throws \Psr\Http\Client\ClientExceptionInterface If an error happens while processing the request.
     */
    public function send(RequestInterface $request): ResponseInterface
    {
        $response = $this->getHttpClient()->sendRequest($request);
        $statusCode = $response->getStatusCode();
        if ($statusCode < 200 || $statusCode > 299) {
            $this->handleRequestError($response);
        }

        return $response;
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function createRequest(RequestMethod|string $method, UriInterface|string $uri, array $options = []): RequestInterface
    {
        return $this
            ->getRequestFactory()
            ->withMethod($method)
            ->withRelativeUri($uri)
            ->withOptions($options)
            ->create();
    }

    /**
     * Make a GET request to the service and return the response.
     */
    public function get(UriInterface|string $uri): ResponseInterface
    {
        return $this->send($this->createRequest(RequestMethod::GET, $uri));
    }

    /**
     * Make a POST request to the service and return the response.
     *
     * @param  array<string, mixed>  $options
     */
    public function post(UriInterface|string $uri, array $options = []): ResponseInterface
    {
        return $this->send($this->createRequest(RequestMethod::POST, $uri, $options));
    }

    /**
     * Make a PUT request to the service and return the response.
     *
     * @param  array<string, mixed>  $options
     */
    public function put(UriInterface|string $uri, array $options = []): ResponseInterface
    {
        return $this->send($this->createRequest(RequestMethod::PUT, $uri, $options));
    }

    /**
     * Make a PUT request to the service and return the response.
     *
     * @param  array<string, mixed>  $options
     */
    public function patch(UriInterface|string $uri, array $options = []): ResponseInterface
    {
        return $this->send($this->createRequest(RequestMethod::PATCH, $uri, $options));
    }

    /**
     * Make a DELETE request to the service and return the response.
     *
     * @param  array<string, mixed>  $options
     */
    public function delete(UriInterface|string $uri, array $options = []): ResponseInterface
    {
        return $this->send($this->createRequest(RequestMethod::DELETE, $uri, $options));
    }

    /**
     * Handle the request error.
     *
     * @throws Exception
     * @throws \EinarHansen\Http\Exceptions\ValidationException
     * @throws \EinarHansen\Http\Exceptions\NotFoundException
     */
    protected function handleRequestError(ResponseInterface $response): void
    {
        if ($response->getStatusCode() == 422) {
            $body = json_decode((string) $response->getBody(), true);
            if (! is_array($body)) {
                $body = [$body];
            }

            throw new ValidationException($body);
        }

        if ($response->getStatusCode() == 404) {
            throw new NotFoundException();
        }

        throw new Exception((string) $response->getBody());
    }

    /**
     * Retry the callback or fail after x seconds.
     *
     * @throws \EinarHansen\Http\Exceptions\TimeoutException
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
