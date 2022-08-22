<?php

declare(strict_types=1);

namespace EinarHansen\Http\Message;

use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\UriFactoryInterface;

trait ManagesUriFactory
{
    protected UriFactoryInterface $uriFactory;

    public function getUriFactory(): UriFactoryInterface
    {
        return $this->uriFactory;
    }

    public function setUriFactory(UriFactoryInterface $uriFactory = null): self
    {
        $this->uriFactory = $uriFactory ?: Psr17FactoryDiscovery::findUriFactory();

        return $this;
    }

    public function withUriFactory(UriFactoryInterface $uriFactory = null): static
    {
        $clone = clone $this;
        $clone->setUriFactory($uriFactory);

        return $clone;
    }
}
