<?php

declare(strict_types=1);

namespace Tests\Feature\Support;

use EinarHansen\Http\Support\AttributeBag;
use PHPUnit\Framework\TestCase;

final class AttributeBagTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_get_a_string(): void
    {
        $attributeBag = new AttributeBag(['string' => '::string::']);

        $this->assertEquals(
            expected: $attributeBag->string('string'),
            actual: '::string::'
        );
    }
}
