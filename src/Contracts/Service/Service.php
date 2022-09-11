<?php

declare(strict_types=1);

namespace EinarHansen\Http\Contracts\Service;

use EinarHansen\Http\Message\RequestFactory;

interface Service
{
    /**
     * Build the Request.
     *
     * @return \EinarHansen\Http\Message\RequestFactory
     */
    public function makeRequest(): RequestFactory;
}
