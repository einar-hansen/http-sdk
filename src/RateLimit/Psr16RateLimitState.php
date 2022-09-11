<?php

declare(strict_types=1);

namespace EinarHansen\Http\RateLimit;

use DateTimeImmutable;
use DateTimeInterface;
use EinarHansen\Http\Contracts\RateLimit\RateLimiterState;
use Psr\SimpleCache\CacheInterface;

class Psr16RateLimitState implements RateLimiterState
{
    public ?DateTimeImmutable $expiresAt = null;

    public int $attempts = 0;

    /**
     * Create a new limit instance. State is tracked in lifecycle.
     *
     * Set attempts before timestamp, since a past timestamp
     * should reset the attempts.
     *
     * @param  string  $cacheKey
     * @param  CacheInterface  $cache
     * @param  int  $maxAttempts
     * @param  int  $decaySeconds
     * @return void
     */
    public function __construct(
        public string $cacheKey,
        public CacheInterface $cache,
        public int $maxAttempts,
        public int $decaySeconds,
        public readonly ?DateTimeImmutable $fakeTime = null,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function getAttempts(): int
    {
        $attempts = $this->cache->get($this->cacheKey, 0);
        if (is_numeric($attempts)) {
            return (int) $attempts;
        }

        return 0;
    }

    /**
     * {@inheritDoc}
     */
    public function setAttempts(int $attempts): self
    {
        if ($attempts < 0) {
            $attempts = 0;
        }

        $this->cache->set(
            $this->cacheKey,
            $attempts,
            $this->getExpiresIn() ?? $this->decaySeconds
        );

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRemaining(): int
    {
        $remaining = $this->maxAttempts - $this->getAttempts();

        if ($remaining > $this->maxAttempts) {
            return $this->maxAttempts;
        }
        if ($this->getAttempts() > $this->maxAttempts) {
            return 0;
        }

        return $remaining;
    }

    /**
     * {@inheritDoc}
     */
    public function setRemaining(int $remaining): self
    {
        $this->setAttempts($this->maxAttempts - $remaining);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getExpiresAt(): ?DateTimeInterface
    {
        $expiresAt = $this->cache->get($this->cacheKey.'.expires-at');
        if (is_null($expiresAt)) {
            return null;
        }
        if (! $expiresAt instanceof DateTimeInterface) {
            return null;
        }
        if ($this->now() > $expiresAt) {
            $this->clear();

            return null;
        }

        return $expiresAt;
    }

    /**
     * {@inheritDoc}
     */
    public function setExpiresAt(DateTimeInterface|null $expiresAt): self
    {
        if (is_null($expiresAt)) {
            $this->cache->delete($this->cacheKey.'.expires-at');

            return $this;
        }
        if (! $expiresAt instanceof DateTimeImmutable) {
            $expiresAt = DateTimeImmutable::createFromInterface(
                object: $expiresAt
            );
        }
        if ($expiresAt > $this->now()) {
            $this->cache->set(
                $this->cacheKey.'.expires-at',
                $expiresAt,
                $this->now()->diff(
                    targetObject: $expiresAt,
                    absolute: true
                ),
            );
        } else {
            $this->clear();
        }

        return $this;
    }

    /**
     * TODO Must verify that periods of over 60 seconds gets return as 60+ seconds
     *
     * {@inheritDoc}
     */
    public function getExpiresIn(): ?int
    {
        if (is_null($expiresAt = $this->getExpiresAt())) {
            return null;
        }

        return (int) $expiresAt->getTimestamp() - $this->now()->getTimestamp();
    }

    /**
     * {@inheritDoc}
     */
    public function setExpiresIn(int|null $expiresIn): self
    {
        if (is_null($expiresIn)) {
            $this->setExpiresAt(expiresAt: null);

            return $this;
        }
        $now = $this->now();
        $this->setExpiresAt($now->setTimestamp($now->getTimestamp() + $expiresIn));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function attempt(callable $callback): mixed
    {
        if ($this->tooManyAttempts()) {
            return false;
        }
        $this->hit();

        return $callback();
    }

    /**
     * Determine if the service has been "accessed" too many times.
     *
     * First we check if we have remaining attempts, if
     * no attempts available then we check if the period is
     * expired.
     *
     * @return bool
     */
    public function tooManyAttempts(): bool
    {
        if ($this->getRemaining() <= 0) {
            if (! $this->isExpired()) {
                return true;
            }
        }

        return false;
    }

    /**
     * A method to check if the rate limit period is expired.
     */
    public function isExpired(): bool
    {
        if (is_null($this->getExpiresAt())) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function hit(): void
    {
        if (is_null($this->getExpiresAt())) {
            $this->setExpiresIn($this->decaySeconds);
        }
        $this->setAttempts($this->getAttempts() + 1);
    }

    /**
     * Clear the hits and lockout timer for the given key.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->cache->deleteMultiple([
            $this->cacheKey,
            $this->cacheKey.'.expires-at',
        ]);
    }

    public function now(): DateTimeImmutable
    {
        if ($this->fakeTime) {
            return $this->fakeTime;
        }

        return new DateTimeImmutable(datetime: 'now');
    }
}
