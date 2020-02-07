<?php

namespace Genesis\BehatApiSpec\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Exception;
use Genesis\BehatApiSpec\Contracts\Endpoint;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use ReflectionClass;

class ApiSpecContext implements Context
{
    private static $mappings;

    private $headers = [];

    public static function setSpecMappings(array $mappings)
    {
        self::$mappings = $mappings;
    }

    private function createRequest(string $method, string $endpoint, array $headers, string $body): RequestInterface
    {
        $request = new Request($method, 'http://localhost:8090/index.php' . $endpoint);

        foreach ($headers as $header => $value) {
            $request->withHeader($header, $value);
        }

        $request->withBody(Psr7\stream_for($body));

        return $request;
    }

    private function getClient(array $config = []): ClientInterface
    {
        return new Client($config);
    }

    /**
     * @Given I set the following headers:
     */
    public function iSetTheFollowingHeaders(TableNode $headers)
    {
        foreach ($headers->getRowsHash() as $header => $value) {
            $this->headers[$header] = $value;
        }
    }

    /**
     * @Then I expect a :statusCode :apiSpec response expecting:
     * @param mixed $statusCode
     * @param mixed $apiSpec
     */
    public function validateResponse($statusCode, $apiSpec)
    {
        $apiSpec = $this->getApiSpecEndpointClass($apiSpec);

        if (!(in_array(EndPoint::class, class_implements($apiSpec)))) {
            throw new Exception('Not an apiSpec class: ' . $apiSec);
        }

        if (!method_exists($apiSpec, 'getSchema')) {
            echo sprintf('Scaffolding schema for endpoint: %s...', $apiSpec);
            $schema = $this->scaffoldSchema($this->getResponseBody());
            $schemaString = $this->suggestSchema($apiSpec, $schema, $this->getStatusCode());
            $this->appendSchemaToEndpointSpec($apiSpec, $schemaString);
        } else {
            echo 'validating....';
            $this->validate($this->getResponseBody(), $apiSpec::getSchema());
        }
    }

    private function appendSchemaToEndpointSpec(string $apiSpec, string $schema)
    {
        $file = $this->getFilename($apiSpec);
        $contents = file_get_contents($file);
        $contents = preg_replace('/(.*)}/su', '${1}' . PHP_EOL . $schema . PHP_EOL . '}', $contents);

        file_put_contents($file, $contents);
    }

    private function getFilename(string $class): string
    {
        return (new ReflectionClass($class))->getFileName();
    }

    private function suggestSchema(string $endpoint, array $schema, int $statusCode): string
    {
        $tab = 1;

        $getSchemaMethod = $this->tab($tab) . 'public static function getSchema(): array' . PHP_EOL;
        $getSchemaMethod .= $this->tab($tab) . '{' . PHP_EOL;
        $getSchemaMethod .= $this->tab($tab+1) . 'return [' . PHP_EOL;
        $getSchemaMethod .= $this->tab($tab+2) . $statusCode . ' => [' . PHP_EOL;
        $getSchemaMethod .= $this->getSchemaPropertiesAsString($schema, 4);
        $getSchemaMethod .= $this->tab($tab+2) . '],' . PHP_EOL;
        $getSchemaMethod .= $this->tab($tab+1) . '];' . PHP_EOL;
        $getSchemaMethod .= $this->tab($tab) . '}';

        return $getSchemaMethod;
    }

    public function tab($count): string
    {
        return str_repeat(' ', $count*4);
    }

    private function getSchemaPropertiesAsString(array $schema, int $tab): string
    {
        $getSchemaMethod = '';
        foreach ($schema as $property => $value) {
            if ($value['type'] === 'object' || $value['type'] === 'array') {
                $getSchemaMethod .= $this->tab($tab) . "'$property' => [" . PHP_EOL;
                $getSchemaMethod .= $this->tab($tab+1) . sprintf("'type' => self::TYPE_%s,", strtoupper($value['type'])) . PHP_EOL;
                $getSchemaMethod .= $this->tab($tab+1) . "'optional' => false," . PHP_EOL;
                $getSchemaMethod .= $this->tab($tab+1) . "'schema' => [" . PHP_EOL;
                $getSchemaMethod .= $this->getSchemaPropertiesAsString($value['schema'], $tab+2);
                $getSchemaMethod .= $this->tab($tab+1) . '],' . PHP_EOL;
                $getSchemaMethod .= $this->tab($tab) . '],' . PHP_EOL;
            } else {
                $getSchemaMethod .= $this->tab($tab) . "'$property' => [" . PHP_EOL;
                $getSchemaMethod .= $this->tab($tab+1) . sprintf("'type' => self::TYPE_%s,", strtoupper($value['type'])) . PHP_EOL;
                $getSchemaMethod .= $this->tab($tab+1) . "'optional' => false," . PHP_EOL;

                switch ($value['type']) {
                    case 'string':
                        $getSchemaMethod .= $this->tab($tab+1) . "'pattern' => false," . PHP_EOL;
                        break;
                    case 'integer':
                        $getSchemaMethod .= $this->tab($tab+1) . "'min' => false," . PHP_EOL;
                        $getSchemaMethod .= $this->tab($tab+1) . "'max' => false," . PHP_EOL;
                        break;
                }

                $getSchemaMethod .= $this->tab($tab) . '],' . PHP_EOL;
            }
        }

        return $getSchemaMethod;
    }

    private function scaffoldSchema(string $body): array
    {
        $response = json_decode($body, true);

        $schema = [];
        foreach ($response as $property => $value) {
            $schema[$property] = ['type' => gettype($value)];
            switch (gettype($value)) {
                case 'array':
                    if (is_string(key($value))) {
                        $schema[$property]['type'] = 'object';
                    }
                    $schema[$property]['schema'] = $this->scaffoldSchema(json_encode($value));
                    break;
            }
        }

        return $schema;
    }

    private function validate(string $body, array $schema)
    {
        echo 'validating....';
        $decodedBody = json_decode($body, true);


    }

    private function getApiSpecEndpointClass(string $class): string
    {
        return self::$mappings['endpoint'] . $class;
    }

    /**
     * @When I make a :method request to :arg1 endpoint with body:
     * @param mixed $method
     * @param mixed $endpoint
     */
    public function sendRequest($method, $endpoint, PyStringNode $body)
    {
        $endpoint = $this->getApiSpecEndpointClass($endpoint);
        $headers = array_merge($this->headers, $endpoint::getHeaders());
        $this->response = $this->getClient()->send(
            $this->createRequest($method, $endpoint::getEndpoint(), $headers, (string) $body)
        );
        $this->resetState();
    }

    private function getResponseBody(): string
    {
        return (string) $this->response->getBody();
    }

    private function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function resetState()
    {
        $this->headers = [];
    }

    public function assertStatusCode()
    {

    }

    public function assertResponse()
    {

    }

    public function assertResponseHeaders()
    {

    }
}

