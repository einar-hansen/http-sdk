<?php

declare(strict_types=1);

namespace EinarHansen\Http\Service;

use EinarHansen\Http\Collection\ArrayCollectionFactory;
use EinarHansen\Http\Collection\CollectionFactoryInterface;
use EinarHansen\Http\Enum\RequestMethod;
use EinarHansen\Http\Exception\NotFoundException;
use EinarHansen\Http\Exception\ValidationException;
use EinarHansen\Http\Message\RequestFactory;
use Exception;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

/**
 * This service is what is called a "Calling Library" in PSR-18.
 *
 * A Calling Library is any code that makes use of a Client. It does
 * not implement this specification's interfaces but consumes an
 * object that implements them (a Client).
 *
 * @author  Einar-Johan Hansen <einar@einarhansen.dev>
 *
 * @see     https://www.php-fig.org/psr/psr-18/
 */
class Service
{
    protected readonly RequestFactory $requestFactory;

    protected readonly CollectionFactoryInterface $collectionFactory;

    /**
     * @throws \Http\Discovery\Exception\NotFoundException If no PSR-18 clients found.
     * @throws \Http\Discovery\Exception\NotFoundException If no PSR-17 factories are found..
     */
    public function __construct(
        ClientInterface $client = null,
        RequestFactoryInterface $requestFactory = null,
        StreamFactoryInterface $streamFactory = null,
        UriFactoryInterface $uriFactory = null,
        CollectionFactoryInterface $collectionFactory = null
    ) {
        $this->collectionFactory = $collectionFactory ?? new ArrayCollectionFactory();
        $this->requestFactory = new RequestFactory(
            client: $client,
            requestFactory: $requestFactory,
            streamFactory: $streamFactory,
            uriFactory: $uriFactory,
        );
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
        $response = $this->getRequestFactory()->sendRequest($request);
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
     * Transform the items of the collection to the given class.
     *
     * @param  array<string, mixed>  $extraData
     * @return  iterable<int, \EinarHansen\Http\Data\DataContract>
     */
    public function makeCollection(
        ResponseInterface $response,
        string $factory,
        string $pointer = null,
        array $extraData = []
    ): iterable {
        return $this->collectionFactory
            ->cast(
                response: $response,
                factory: $factory,
                pointer: $pointer,
                extraData: $extraData,
            );
    }

    /**
     * Handle the request error.
     *
     * @throws Exception
     * @throws \EinarHansen\Http\Exception\ValidationException
     * @throws \EinarHansen\Http\Exception\NotFoundException
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
}
