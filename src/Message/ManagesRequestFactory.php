<?php

declare(strict_types=1);

namespace EinarHansen\Http\Message;

use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\RequestFactoryInterface;

trait ManagesRequestFactory
{
    protected RequestFactoryInterface $requestFactory;

    public function getRequestFactory(): RequestFactoryInterface
    {
        return $this->requestFactory;
    }

    public function setRequestFactory(RequestFactoryInterface $requestFactory = null): self
    {
        $this->requestFactory = $requestFactory ?: Psr17FactoryDiscovery::findRequestFactory();

        return $this;
    }

    public function withRequestFactory(RequestFactoryInterface $requestFactory = null): static
    {
        $clone = clone $this;
        $clone->setRequestFactory($requestFactory);

        return $clone;
    }
}
