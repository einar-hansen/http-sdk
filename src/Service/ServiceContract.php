<?php

declare(strict_types=1);

namespace EinarHansen\Http\Service;

use EinarHansen\Http\Message\RequestFactory;

interface ServiceContract
{
    /**
     * Build the Request.
     *
     * @return \EinarHansen\Http\Message\RequestFactory
     */
    public function makeRequest(): RequestFactory;
}
