<?php

declare(strict_types=1);

namespace Tests\Support;

use EinarHansen\Http\Support\AttributeBag;
use PHPUnit\Framework\TestCase;

final class AttributeBagTest extends TestCase
{
    /**
     * @test
     */
    public function testCanBeCreatedFromValidEmailAddress(): void
    {
        $attributeBag = new AttributeBag(['string' => '::string::']);

        $this->assertEquals(
            expected: $attributeBag->string('string'),
            actual: '::string::'
        );
    }
}
