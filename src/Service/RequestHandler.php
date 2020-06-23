<?php

namespace Genesis\BehatApiSpec\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class RequestHandler
{
    private static $response;

    private static $request;

    private static $baseUrl;

    public static function setBaseUrl(string $baseUrl)
    {
        self::$baseUrl = $baseUrl;
    }

    public static function getBaseUrl(): string
    {
        return self::$baseUrl;
    }

    public static function sendRequest(string $method, string $endpoint, array $headers, string $body): void
    {
        try {
            self::$request = self::createRequest($method, $endpoint, $headers, $body);
            self::$response = self::getClient()->send(self::$request);
        } catch (ServerException $e) {
            self::$response = new Response(500);
        }
    }

    public static function getMethod(): ?string
    {
        return self::$request ? self::$request->getMethod() : null;
    }

    public static function getResponseBody(): ?string
    {
        return self::$response ? (string) self::$response->getBody() : null;
    }

    public static function getStatusCode(): ?int
    {
        return self::$response ? self::$response->getStatusCode() : null;
    }

    public static function getHeaders(): array
    {
        return self::$response ? self::$response->getHeaders() : [];
    }

    public static function getRequestHeaders(): array
    {
        return self::$request ? self::$request->getHeaders() : [];
    }

    public static function getRequestBody()
    {
        return self::$request ? self::$request->getBody() : null;
    }

    public static function getUri(): ?UriInterface
    {
        return self::$request ? self::$request->getUri() : null;
    }

    private static function getClient(array $config = []): ClientInterface
    {
        $config['http_errors'] = false;

        return new Client($config);
    }

    private static function createRequest(string $method, string $endpoint, array $headers, string $body): RequestInterface
    {
        return new Request($method, self::$baseUrl . $endpoint, $headers, Psr7\stream_for($body));
    }
}
