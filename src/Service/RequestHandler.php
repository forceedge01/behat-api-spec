<?php

namespace Genesis\BehatApiSpec\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7;
use Psr\Http\Message\RequestInterface;

class RequestHandler
{
    private static $response;

    public static function sendRequest(string $method, string $endpoint, array $headers, string $body): void
    {
        self::$response = self::getClient()->send(
            self::createRequest($method, $endpoint, $headers, $body)
        );
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
        return new Client($config);
    }

    private static function createRequest(string $method, string $endpoint, array $headers, string $body): RequestInterface
    {
        $request = new Request($method, 'http://localhost:8090/index.php' . $endpoint);

        foreach ($headers as $header => $value) {
            $request->withHeader($header, $value);
        }

        $request->withBody(Psr7\stream_for($body));

        return $request;
    }
}
