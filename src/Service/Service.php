<?php

declare(strict_types=1);

namespace EinarHansen\Http\Service;

use EinarHansen\Http\Collection\ArrayCollectionFactory;
use EinarHansen\Http\Contracts\Collection\CollectionFactory;
use EinarHansen\Http\Message\RequestFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

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

    protected readonly CollectionFactory $collectionFactory;

    /**
     * @throws \Http\Discovery\Exception\NotFoundException If no PSR-18 clients found.
     * @throws \Http\Discovery\Exception\NotFoundException If no PSR-17 factories are found..
     */
    public function __construct(
        ClientInterface $client = null,
        RequestFactoryInterface $requestFactory = null,
        StreamFactoryInterface $streamFactory = null,
        UriFactoryInterface $uriFactory = null,
        CollectionFactory $collectionFactory = null
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
}
