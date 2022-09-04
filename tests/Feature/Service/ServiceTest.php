<?php

declare(strict_types=1);

namespace Tests\Feature\Service;

use EinarHansen\Http\Service\ServiceContract;
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
            expected: ServiceContract::class,
            actual: $service
        );
    }
}
