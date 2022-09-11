<?php

declare(strict_types=1);

namespace EinarHansen\Http\Contracts\Service;

use EinarHansen\Http\Contracts\RateLimit\RateLimiterState;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface RateLimited
{
    /**
     * Get the rate limit state.
     *
     * @return \EinarHansen\Http\Contracts\RateLimit\RateLimiterState
     */
    public function getRateLimitState(): RateLimiterState;

    /**
     * Attempts to execute a callback if it's not limited. If the
     * limit is reached it will return false.
     *
     * @param  \Psr\Http\Message\RequestInterface  $request
     * @return  \Psr\Http\Message\ResponseInterface|false
     */
    public function attempt(RequestInterface $request): ResponseInterface|false;
}
