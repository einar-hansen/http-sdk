<?php

declare(strict_types=1);

namespace EinarHansen\Http\Contracts\Service;

use EinarHansen\Http\Contracts\RateLimit\RateLimiterState;

interface RateLimited
{
    /**
     * Get the rate limit state.
     *
     * @return \EinarHansen\Http\Contracts\RateLimit\RateLimiterState
     */
    public function getRateLimitState(): RateLimiterState;
}
