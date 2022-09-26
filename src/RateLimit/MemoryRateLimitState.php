<?php

declare(strict_types=1);

namespace EinarHansen\Http\RateLimit;

use DateTimeImmutable;
use DateTimeInterface;
use EinarHansen\Http\Contracts\RateLimit\RateLimiterState;

class MemoryRateLimitState implements RateLimiterState
{
    public ?DateTimeImmutable $expiresAt = null;

    public int $attempts = 0;

    /**
     * Create a new limit instance. State is tracked in lifecycle.
     *
     * Set attempts before timestamp, since a past timestamp
     * should reset the attempts.
     *
     * @param  int  $maxAttempts
     * @param  int  $decaySeconds
     * @param  int  $attempts
     * @param  DateTimeInterface|int  $expiresAt
     * @return void
     */
    public function __construct(
        public int $maxAttempts,
        public int $decaySeconds,
        int $attempts = 0,
        DateTimeInterface|int $expiresAt = null,
        public readonly ?DateTimeImmutable $fakeTime = null,
    ) {
        $this->setAttempts($attempts);
        if (is_int($expiresAt)) {
            $this->setExpiresIn($expiresAt);
        } elseif ($expiresAt instanceof DateTimeInterface) {
            $this->setExpiresAt($expiresAt);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getAttempts(): int
    {
        return $this->attempts;
    }

    /**
     * {@inheritDoc}
     */
    public function setAttempts(int $attempts): self
    {
        if ($attempts < 0) {
            $attempts = 0;
        }
        $this->attempts = $attempts;

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
        if (is_null($this->expiresAt)) {
            return null;
        }
        if ($this->now() > $this->expiresAt) {
            $this->clear();
        }

        return $this->expiresAt;
    }

    /**
     * {@inheritDoc}
     */
    public function setExpiresAt(DateTimeInterface|null $expiresAt): self
    {
        if (is_null($expiresAt)) {
            $this->expiresAt = null;

            return $this;
        }
        if (! $expiresAt instanceof DateTimeImmutable) {
            $expiresAt = DateTimeImmutable::createFromInterface(
                object: $expiresAt
            );
        }
        if ($expiresAt > $this->now()) {
            $this->expiresAt = $expiresAt;
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
        $this->attempts++;
    }

    /**
     * Clear the hits and lockout timer for the given key.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->setAttempts(attempts: 0);
        $this->setExpiresAt(expiresAt: null);
    }

    public function now(): DateTimeImmutable
    {
        if ($this->fakeTime) {
            return $this->fakeTime;
        }

        return new DateTimeImmutable(datetime: 'now');
    }

    /**
     * Grab an array that show details about the current state.
     *
     * @return array{
     *         expiresAt: ?DateTimeInterface,
     *         expiresIn: ?int,
     *         attempts: int,
     *         remaining: int,
     * }
     */
    public function toArray(): array
    {
        return [
            'expiresAt' => $this->getExpiresAt(),
            'expiresIn' => $this->getExpiresIn(),
            'attempts' => $this->getAttempts(),
            'remaining' => $this->getRemaining(),
        ];
    }
}
