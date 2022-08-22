<?php

declare(strict_types=1);

namespace EinarHansen\Http\Message;

use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\StreamFactoryInterface;

trait ManagesStreamFactory
{
    protected StreamFactoryInterface $streamFactory;

    public function getStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory;
    }

    public function setStreamFactory(StreamFactoryInterface $streamFactory = null): self
    {
        $this->streamFactory = $streamFactory ?: Psr17FactoryDiscovery::findStreamFactory();

        return $this;
    }

    public function withStreamFactory(StreamFactoryInterface $streamFactory = null): static
    {
        $clone = clone $this;
        $clone->setStreamFactory($streamFactory);

        return $clone;
    }
}
