<?php

namespace Genesis\BehatApiSpec\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

class RequestHandler
{
    private static $response;

    private static $baseUrl;

    public static function setBaseUrl(string $baseUrl)
    {
        self::$baseUrl = $baseUrl;
    }

    public static function sendRequest(string $method, string $endpoint, array $headers, string $body): void
    {
        try {
            self::$response = self::getClient()->send(
                self::createRequest($method, $endpoint, $headers, $body)
            );
        } catch (ServerException $e) {
            self::$response = new Response(500);
        }
    }

    public static function getResponseBody(): string
    {
        return (string) self::$response->getBody();
    }

    public static function getStatusCode(): int
    {
        return self::$response->getStatusCode();
    }

    public static function getHeaders(): array
    {
        return self::$response->getHeaders();
    }

    private static function getClient(array $config = []): ClientInterface
    {
        $config['http_errors'] = false;

        return new Client($config);
    }

    private static function createRequest(string $method, string $endpoint, array $headers, string $body): RequestInterface
    {
        $request = new Request($method, self::$baseUrl . $endpoint);

        foreach ($headers as $header => $value) {
            $request->withHeader($header, $value);
        }

        $request->withBody(Psr7\stream_for($body));

        return $request;
    }
}
