<?php

declare(strict_types=1);

namespace Tests\Feature\Service;

use EinarHansen\Http\Contracts\Service\Service;
use PHPUnit\Framework\TestCase;
use Tests\Service\PublicApiService;

class ServiceTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_instanciate_a_service(): void
    {
        $service = new PublicApiService();

        $this->assertInstanceOf(
            expected: Service::class,
            actual: $service
        );
    }
}
