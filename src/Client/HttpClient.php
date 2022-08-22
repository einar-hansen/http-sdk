<?php

declare(strict_types=1);

namespace EinarHansen\Http\Client;

use EinarHansen\Http\Enums\StatusCode;
use EinarHansen\Http\Exceptions\FailedRequestException;
use EinarHansen\Http\Exceptions\NotFoundException;
use EinarHansen\Http\Exceptions\UnauthorizedException;
use EinarHansen\Http\Exceptions\ValidationException;
use Exception;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpClient implements ClientInterface
{
    protected ClientInterface $client;

    public function __construct(
        ClientInterface $client = null
    ) {
        $this->client = $client ?: Psr18ClientDiscovery::find();
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    /**
     * {@inheritDoc}
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->client->sendRequest($request);
    }

    public static function isSuccessful(ResponseInterface $response): bool
    {
        return (int) substr((string) $response->getStatusCode(), 0, 1) === 2;
    }

    public static function getContent(ResponseInterface $response)
    {
        $body = $response->getBody()->__toString();
        if (strpos($response->getHeaderLine('Content-Type'), 'application/json') === 0) {
            $content = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $content;
            }
        }

        return $body;
    }

    /**
     * @throws Exception
     */
    protected function handleRequestError(ResponseInterface $response): void
    {
        $statusCode = StatusCode::tryFrom($response->getStatusCode());

        if ($statusCode === StatusCode::HTTP_BAD_REQUEST) {
            throw new FailedRequestException((string) $response->getBody());
        }

        if ($statusCode === StatusCode::HTTP_UNAUTHORIZED) {
            throw new UnauthorizedException((string) $response->getBody());
        }

        if ($statusCode === StatusCode::HTTP_NOT_FOUND) {
            throw new NotFoundException();
        }

        if ($statusCode === StatusCode::HTTP_UNPROCESSABLE_ENTITY) {
            $array = json_decode((string) $response->getBody(), true);

            throw new ValidationException(is_array($array) ? $array : []);
        }

        throw new Exception((string) $response->getBody());
    }
}
