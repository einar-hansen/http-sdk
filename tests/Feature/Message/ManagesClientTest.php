<?php

declare(strict_types=1);

namespace Tests\Feature\Message;

use EinarHansen\Http\Service\ServiceContract;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tests\Service\PublicApiService;

class ManagesClientTest extends TestCase
{
    public ServiceContract $service;

    protected function setUp(): void
    {
        $this->service = new PublicApiService();
    }

    /**
     * @test
     */
    public function it_can_send_a_get_request(): void
    {
        $response = $this->service
            ->makeRequest()
            ->get(
                url: '/entries',
                query: ['title' => 'at']
            );

        $this->assertInstanceOf(
            expected: ResponseInterface::class,
            actual: $response
        );
        $this->assertEquals(
            expected: 200,
            actual: $response->getStatusCode()
        );
    }

    /**
     * @test
     */
    public function it_can_create_a_post_request_with_json_body(): void
    {
        $body = [
            'Id' => 12345,
            'Customer' => 'John Smith',
            'Quantity' => 1,
            'Price' => 10.00,
        ];
        $request = $this->service
            ->makeRequest()
            ->withMethod('POST')
            ->withJson(body: $body)
            ->create();

        $this->assertInstanceOf(
            expected: RequestInterface::class,
            actual: $request
        );
        $this->assertEquals(
            expected: 'application/json',
            actual: $request->getHeaderLine('Content-Type')
        );
        $this->assertEquals(
            expected: $body,
            actual: json_decode(json: (string) $request->getBody(), associative: true)
        );
    }

    /**
     * @test
     */
    public function it_can_create_a_post_request_with_form_body(): void
    {
        $body = [
            'Id' => 12345,
            'Customer' => 'John Smith',
            'Quantity' => 1,
            'Price' => 10.00,
        ];
        $request = $this->service
            ->makeRequest()
            ->withMethod('POST')
            ->withForm(body: $body)
            ->create();

        $this->assertInstanceOf(
            expected: RequestInterface::class,
            actual: $request
        );
        $this->assertEquals(
            expected: 'application/x-www-form-urlencoded',
            actual: $request->getHeaderLine('Content-Type')
        );
        $this->assertEquals(
            expected: 'Id=12345&Customer=John+Smith&Quantity=1&Price=10',
            actual: (string) $request->getBody()
        );
    }
}
