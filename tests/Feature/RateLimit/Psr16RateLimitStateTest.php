<?php

declare(strict_types=1);

namespace Tests\Feature\RateLimit;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use EinarHansen\Http\Contracts\RateLimit\RateLimiterState;
use EinarHansen\Http\RateLimit\Psr16RateLimitState;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;

final class Psr16RateLimitStateTest extends TestCase
{
    public RateLimiterState $rateLimitState;

    public DateTimeImmutable $now;

    public function setUp(): void
    {
        $psr6Cache = new FilesystemAdapter();
        $psr16Cache = new Psr16Cache($psr6Cache);
        $this->now = new DateTimeImmutable('now');

        $this->rateLimitState = new Psr16RateLimitState(
            cacheKey: 'phpunit',
            cache: $psr16Cache,
            maxAttempts: 10,
            decaySeconds: 60,
            fakeTime: $this->now
        );
    }

    /**
     * @test
     * @dataProvider provideStateAttemptsData
     */
    public function it_can_calculate_attempts_and_remaining_attemps(
        int $attempts,
        int $expectedAttempts,
        int $expectedRemaining
    ): void {
        $this->rateLimitState->setAttempts($attempts);
        $this->assertSame(
            expected: $expectedAttempts,
            actual: $this->rateLimitState->getAttempts()
        );
        $this->assertSame(
            expected: $expectedRemaining,
            actual: $this->rateLimitState->getRemaining()
        );
    }

    public function provideStateAttemptsData()
    {
        return [
            'zero attempts' => [0, 0, 10],
            'three attempts' => [3, 3,  7],
            'too many attempts' => [12, 12,  0],
            'negative attempts' => [-5, 0, 10],
        ];
    }

    /**
     * @test
     * @dataProvider provideExpirationStateData
     */
    public function it_can_handle_expiration_time(
        DateTimeInterface|int|null $expiresAt,
        DateTimeInterface|int|null $expectedExpiresAt,
        int|null $expectedExpiresIn
    ): void {
        if (is_null($expiresAt) || $expiresAt instanceof DateTimeInterface) {
            $this->rateLimitState->setExpiresAt($expiresAt);
        } else {
            $this->rateLimitState->setExpiresIn($expiresAt);
        }
        $this->assertEquals(
            expected: $expectedExpiresAt,
            actual: $this->rateLimitState->getExpiresAt()
        );
        $this->assertSame(
            expected: $expectedExpiresIn,
            actual: $this->rateLimitState->getExpiresIn()
        );
    }

    public function provideExpirationStateData()
    {
        $now = new DateTimeImmutable('now');

        return [
            'null date' => [null, null, null],
            'past time' => [$now->sub(interval: new DateInterval('PT40S')), null,  null],
            'future time' => [$future = $now->add(interval: new DateInterval('PT40S')), $future,  40],
            'in one hour' => [$future = $now->add(interval: new DateInterval('PT1H')), $future,  3600],
            '0 seconds' => [$now, null, null],
            'negative 10 seconds' => [-10, null, null],
            'future 10 seconds' => [10, $now->setTimestamp($now->getTimestamp() + 10), 10],
            'date time object' => [$future = new DateTime('+40 seconds'), $future, 40],
        ];
    }

    /**
     * @test
     * @dataProvider provideAttemptStateData
     */
    public function it_can_handle_attempts(
        int $attempts,
        DateTimeInterface|int|null $expiresAt,
        callable $callback,
        mixed $expectedCallbackResult,
        mixed $expectedAttempts,
        mixed $expectedExpiresIn,
    ): void {
        $this->rateLimitState->setAttempts($attempts);
        if (is_null($expiresAt) || $expiresAt instanceof DateTimeInterface) {
            $this->rateLimitState->setExpiresAt($expiresAt);
        } else {
            $this->rateLimitState->setExpiresIn($expiresAt);
        }

        $this->assertSame(
            expected: $expectedCallbackResult,
            actual: $this->rateLimitState->attempt($callback)
        );
        $this->assertSame(
            expected: $expectedAttempts,
            actual: $this->rateLimitState->getAttempts()
        );
        $this->assertSame(
            expected: $expectedExpiresIn,
            actual: $this->rateLimitState->getExpiresIn()
        );
    }

    public function provideAttemptStateData()
    {
        $now = new DateTimeImmutable('now');
        $callback = fn () => 'SUCCESS';

        return [
            'zero attempts' => [0, 10, $callback, 'SUCCESS', 1, 10],
            'max attempts' => [10, 10, $callback, false, 10, 10],
            'too many attempts' => [15, 10, $callback, false, 15, 10],
            '0 seconds' => [9, 0, $callback, 'SUCCESS', 1, 60],
            'negative 10 seconds' => [9, -10, $callback, 'SUCCESS', 1, 60],
            'negative timestamp' => [9, $now->sub(interval: new DateInterval('PT40S')), $callback, 'SUCCESS', 1, 60],
        ];
    }
    // Add test for when it has a previous state
}
