<?php

declare(strict_types=1);

namespace EinarHansen\Http\Contracts\RateLimit;

use DateTimeInterface;

/**
 * Contract for a rate limit state that you should use
 * for rate limited services.
 *
 * Interface is strongly inspired by Laravel's RateLimiter class.
 *
 * In userland you should use "attempt". If the response is not
 * false, then you can continue. If you need to set attempts/remaining
 * or decay time from a service after an attempt, you should use the
 * "setAttemps"/"setRemaining" along with "setExpiresAt"/"setExpiresIn"
 * this will update the state and be used with your subsecuent calls.
 */
interface RateLimiterState
{
    /**
     * Get the number of attempts for the given key.
     *
     * @return int
     */
    public function getAttempts(): int;

    /**
     * Set the number of attempts for the given key.
     *
     * This method should affect the result from getRemaining()
     *
     * @param  int  $attemps
     * @return self
     */
    public function setAttempts(int $attemps): self;

    /**
     * Get the number of attempts left for the given key.
     *
     * @return int
     */
    public function getRemaining(): int;

    /**
     * Set the number of attempts left for the given key.
     *
     * This method should affect the result from getAttempts()
     *
     * @param  int  $remaining
     * @return self
     */
    public function setRemaining(int $remaining): self;

    /**
     * Get the timestamp for when the rate limit period is cleared.
     * If the timestamp is passed it should return null. Can only
     * return values in the future.
     *
     * @return DateTimeInterface|null
     */
    public function getExpiresAt(): ?DateTimeInterface;

    /**
     * Set the timestamp for when the rate limit period is cleared.
     *
     * @param  DateTimeInterface|null  $expiresAt
     * @return self
     */
    public function setExpiresAt(DateTimeInterface|null $expiresAt): self;

    /**
     * Get the number of seconds until the rate limit period is cleared.
     *
     * @return int|null
     */
    public function getExpiresIn(): ?int;

    /**
     * Set  number of seconds until the service rate limit period
     * is cleared.
     *
     * @param  int|null  $expiresAt
     * @return self
     */
    public function setExpiresIn(int|null $expiresAt): self;

    /**
     * Try to execute the callback.
     *
     * Return false if the attempt could not be executed.
     *
     * @param  callable  $callback
     * @return mixed
     */
    public function attempt(callable $callback): mixed;

    /**
     * Determine if the service has been "accessed" too many times.
     *
     * @return bool
     */
    public function tooManyAttempts(): bool;

    /**
     * This method MUST clear the attempts and expiration.
     *
     * @return void
     */
    public function clear(): void;
}
