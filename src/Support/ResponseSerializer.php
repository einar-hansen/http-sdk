<?php

declare(strict_types=1);

namespace EinarHansen\Http\Support;

use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

/*
 * This class gives you to possibility to save and load responses from files
 */
class ResponseSerializer
{
    protected ResponseFactoryInterface $responseFactory;

    protected StreamFactoryInterface $streamFactory;

    public function __construct(
        ResponseFactoryInterface $responseFactory = null,
        StreamFactoryInterface $streamFactory = null,
    ) {
        $this->responseFactory = $responseFactory ?: Psr17FactoryDiscovery::findResponseFactory();
        $this->streamFactory = $streamFactory ?: Psr17FactoryDiscovery::findStreamFactory();
    }

    public function set(
        string $filename,
        ResponseInterface $response,
    ): bool {
        $type = $response->getHeader('content-type')[0] ?? '';
        if (static::isTextResponse(header: $type)) {
            $body = (string) $response->getBody();
        } else {
            $body = base64_encode(string: (string) $response->getBody());
        }

        return file_put_contents(
            filename: $filename,
            data: json_encode(value: [
                'status' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
                'body' => $body,
                'version' => $response->getProtocolVersion(),
                'reason' => $response->getReasonPhrase(),
            ])
        ) !== false;
    }

    public function get(string $filename): ResponseInterface
    {
        $content = file_get_contents(filename: $filename);
        /**
         * @var  array{
         *     status: int,
         *     headers: string[][],
         *     body: string,
         *     version: string,
         *     reason: string,
         * } $data
         */
        $data = json_decode(
            json:  $content ? $content : '',
            associative: true
        );

        foreach (['content-type', 'Content-Type'] as $key) {
            if (isset($data['headers'][$key])) {
                $type = $data['headers'][$key][0];

                continue;
            }
        }
        if (! static::isTextResponse($type ?? '')) {
            $data['body'] = base64_decode(string: $data['body']);
        }

        $response = $this->responseFactory
            ->createResponse(
                $data['status'],
                $data['reason']
            );
        foreach ($data['headers'] as $name => $value) {
            $response = $response->withHeader($name, $value);
        }
        $response = $response->withBody(
            $this->streamFactory->createStream($data['body'])
        );

        return $response->withProtocolVersion($data['version']);
    }

    public function has(string $filename): bool
    {
        return file_exists(filename: $filename);
    }

    public static function isTextResponse(string $header): bool
    {
        return str_contains(
            haystack: $header,
            needle: 'text'
        ) || str_contains(
            haystack: $header,
            needle: 'json'
        );
    }
}
