<?php

declare(strict_types=1);

namespace Tests\Feature\RateLimit;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use EinarHansen\Http\RateLimit\MemoryRateLimitState;
use PHPUnit\Framework\TestCase;

final class MemoryRateLimitStateTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideStateAttemptsData
     */
    public function it_can_calculate_attempts_and_remaining_attemps(
        int $attempts,
        int $expectedAttempts,
        int $expectedRemaining
    ): void {
        $state = new MemoryRateLimitState(
            maxAttempts: 10,
            decaySeconds: 60,
            attempts: $attempts,
            expiresAt: new DateTimeImmutable('+60 seconds')
        );
        $this->assertSame(
            expected: $expectedAttempts,
            actual: $state->getAttempts()
        );
        $this->assertSame(
            expected: $expectedRemaining,
            actual: $state->getRemaining()
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
        DateTimeInterface $fakeTime,
        DateTimeInterface|int|null $expiresAt,
        DateTimeInterface|int|null $expectedExpiresAt,
        int|null $expectedExpiresIn
    ): void {
        $state = new MemoryRateLimitState(
            maxAttempts: 10,
            decaySeconds: 60,
            attempts: 5,
            expiresAt: $expiresAt,
            fakeTime: $fakeTime
        );
        $this->assertEquals(
            expected: $expectedExpiresAt,
            actual: $state->getExpiresAt()
        );
        $this->assertSame(
            expected: $expectedExpiresIn,
            actual: $state->getExpiresIn()
        );
    }

    public function provideExpirationStateData()
    {
        $now = new DateTimeImmutable('now');

        return [
            'null date' => [$now, null, null, null],
            'past time' => [$now, $now->sub(interval: new DateInterval('PT40S')), null,  null],
            'future time' => [$now, $future = $now->add(interval: new DateInterval('PT40S')), $future,  40],
            'in one hour' => [$now, $future = $now->add(interval: new DateInterval('PT1H')), $future,  3600],
            '0 seconds' => [$now, $now, null, null],
            'negative 10 seconds' => [$now, -10, null, null],
            'future 10 seconds' => [$now, 10, $now->setTimestamp($now->getTimestamp() + 10), 10],
            'date time object' => [$now, $future = new DateTime('+40 seconds'), $future, 40],
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
        $state = new MemoryRateLimitState(
            maxAttempts: 10,
            decaySeconds: 60,
            attempts: $attempts,
            expiresAt: $expiresAt
        );
        $this->assertSame(
            expected: $expectedCallbackResult,
            actual: $state->attempt($callback)
        );
        $this->assertSame(
            expected: $expectedAttempts,
            actual: $state->getAttempts()
        );
        $this->assertSame(
            expected: $expectedExpiresIn,
            actual: $state->getExpiresIn()
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
    // Then run the same suite on the other two classes
}
