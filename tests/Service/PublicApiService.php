<?php

namespace Tests\Service;

use EinarHansen\Http\Contracts\Service\Service as ServiceContract;
use EinarHansen\Http\Message\RequestFactory;
use EinarHansen\Http\Service\Service;

class PublicApiService extends Service implements ServiceContract
{
    public function __construct(
        public readonly string $baseUri = 'https://api.publicapis.org',
    ) {
        parent::__construct();
    }

    public function makeRequest(): RequestFactory
    {
        return $this->getRequestFactory()
            ->withUri(
                uri: $this->baseUri
            )->withHeader(
                name: 'Accept',
                value: 'application/json'
            );
    }
}
