<?php

namespace Genesis\BehatApiSpec\Entity;

use Genesis\BehatApiSpec\Traits\SampleRequestGeneratorTrait;
use ReflectionClass;

class Endpoint
{
    use SampleRequestGeneratorTrait;

    private $endpointClass;

    public function __construct(string $endpointClass)
    {
        $this->endpointClass = $endpointClass;
    }

    public function getResponseSchemas(): array
    {
        return $this->endpointClass::getResponseSchema();
    }

    public function getClassName(): string
    {
        return (new ReflectionClass($this->endpointClass))->getShortName();
    }

    public function getResponseSchemasByMethod(string $method): array
    {
        return $this->getResponseSchemas()[$method];
    }

    public function getResponseSchema(string $method, int $statusCode): array
    {
        return $this->getResponseSchemasByMethod($method)[$statusCode];
    }

    public function getEndpoint(): string
    {
        return $this->endpointClass::getEndpoint();
    }

    public function getRequestQueryParams(): array
    {
        return $this->getRequestSchema()['queryParams'];
    }

    public function getRequestSchema(): array
    {
        return $this->endpointClass::getRequestSchema();
    }

    public function getDefaultHeaders(): array
    {
        return $this->endpointClass::getHeaders();
    }

    public function getRequestQueryParamsAsString(): string
    {
        $string = '?';
        foreach ($this->getRequestQueryParams() as $param => $details) {
            $string .= $param . '=' . urlencode($details['example']) . '&';
        }

        return trim($string, '&');
    }

    public function getSampleRequests(): array
    {
        $requests = [];

        foreach ($this->getResponseSchemas() as $method => $schema) {
            foreach ($this->getResponseSchemasByMethod($method) as $statusCode => $schema) {
                $requests[$method][0] = $this->getSampleCurlRequest(
                    $method,
                    $this->getDefaultHeaders(),
                    '',
                    $this->getEndpoint() . (strtoupper($method) === 'GET' ? $this->getRequestQueryParamsAsString() : ''),
                    htmlspecialchars('<ApiEndpoint>')
                );
            }
        }

        return $requests;
    }
}
