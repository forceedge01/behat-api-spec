<?php

namespace Genesis\BehatApiSpec\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Exception;
use FailAid\Context\FailureContext;
use Genesis\BehatApiSpec\Contracts\Endpoint;
use Genesis\BehatApiSpec\Entity\Schema;
use Genesis\BehatApiSpec\Exception\RequiredPropertyMissingException;
use Genesis\BehatApiSpec\Service\EndpointProvider;
use Genesis\BehatApiSpec\Service\PlaceholderService;
use Genesis\BehatApiSpec\Service\RequestHandler;
use Genesis\BehatApiSpec\Service\SchemaGenerator;
use Genesis\BehatApiSpec\Service\TypeValidator;
use Genesis\BehatApiSpec\Traits\JsonValidateTrait;
use Genesis\BehatApiSpec\Traits\SampleRequestGeneratorTrait;
use Genesis\BehatApiSpec\Traits\SchemaGeneratorTrait;
use Genesis\BehatApiSpec\Traits\SnapshotTrait;
use Genesis\BehatApiSpec\Traits\VersionTrait;
use Genesis\BehatApiSpec\Validators;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Assert;

class ApiSpecContext implements Context
{
    use JsonValidateTrait;
    use SnapshotTrait;
    use SampleRequestGeneratorTrait;
    use SchemaGeneratorTrait;
    use VersionTrait;

    private static $mappings;

    private static $currentScenario;

    private static $options = [];

    private static $setFailStates = false;

    private $headers = [];

    private $body = '';

    private $preRequestCallable;

    private $postRequestCallable;

    /**
     * @param string|null $preRequestCallable Static callable <class>::<method> Receives body and headers and expects return both.
     * @param string|null $postRequestCallable Static callable <class>::<method> Receives body and headers
     */
    public function __construct(string $preRequestCallable = null, string $postRequestCallable = null)
    {
        $this->preRequestCallable = $preRequestCallable ?: function($body, $headers, $url){
            return [
                PlaceholderService::resolveInString($body),
                $headers,
                PlaceholderService::resolveInString($url)
            ];
        };
        $this->postRequestCallable = $postRequestCallable ?: function($body, $headers): void{};
    }

    public static function setFailStates($bool): void
    {
        self::$setFailStates = $bool;
    }

    /**
     * Prevents scenario bleeds.
     *
     * @afterScenario
     */
    public function resetPlaceholders(): void
    {
        PlaceholderService::reset();
    }

    public static function setSpecOptions(string $baseUrl, array $mappings, array $options = ['stripSpaces' => false]): void
    {
        RequestHandler::setBaseUrl($baseUrl);
        EndpointProvider::setBaseNamespace($mappings['endpoint']);
        EndpointProvider::setBasePath($mappings['path'] ?? '');
        self::$mappings = $mappings;
        self::$options = $options;
    }

    private function getOption($key)
    {
        if (isset(self::$options[$key])) {
            return self::$options[$key];
        }

        return null;
    }

    public static function registerInternalTypes(): void
    {
        TypeValidator::registerValidator(Endpoint::TYPE_STRING, Validators\StringValidator::class);
        TypeValidator::registerValidator(Endpoint::TYPE_INTEGER, Validators\IntegerValidator::class);
        TypeValidator::registerValidator(Endpoint::TYPE_NUMBER, Validators\NumberValidator::class);
        TypeValidator::registerValidator(Endpoint::TYPE_BOOLEAN, Validators\BooleanValidator::class);
        TypeValidator::registerValidator(Endpoint::TYPE_ARRAY, Validators\ArrayValidator::class);
        TypeValidator::registerValidator(Endpoint::TYPE_OBJECT, Validators\ObjectValidator::class);
        TypeValidator::registerValidator(Endpoint::TYPE_ENUM, Validators\EnumValidator::class);
        TypeValidator::registerValidator(Endpoint::TYPE_CALLBACK, Validators\CallbackValidator::class);
    }

    /**
     * @BeforeScenario
     * @param mixed $scope
     */
    public function setCurrentScenario($scope): void
    {
        self::$currentScenario = $scope;
    }

    /**
     * @Given I set the following headers:
     */
    public function iSetTheFollowingHeaders(TableNode $headers): void
    {
        foreach ($headers->getRowsHash() as $header => $value) {
            $this->headers[$header] = $value;
        }
    }

    /**
     * @Given I set the following body:
     */
    public function iSetTheFollowingBody(PyStringNode $body): void
    {
        $this->body = $this->getOption('stripSpaces') ? preg_replace('/\s+/', '', (string) $body) : (string) $body;
    }

    /**
     * @When I make a :method request to the :arg1 endpoint
     * @When I make a :method request to the :arg1 endpoint with body:
     * @When I make a :method request to the :arg1 endpoint with query string :queryString
     * @When I make a :method request to the :arg1 endpoint with query string :queryString and body:
     */
    public function sendRequest(string $method, string $endpoint, PyStringNode $body = null, string $queryString = null): void
    {
        $endpoint = EndpointProvider::getApiSpecEndpointClass($endpoint);
        $headers = array_merge($this->headers, $endpoint::getRequestHeaders());
        $url = $endpoint::getEndpoint() . ($queryString ? '?' . $queryString : '');
        if ($body) {
            $body = $this->getOption('stripSpaces') ? preg_replace('/\s+/', '', (string) $body) : (string) $body;
        } else {
            $body = $this->body;
        }

        $preRequestCallable = $this->preRequestCallable;
        list ($body, $headers, $url) = $preRequestCallable($body, $headers, $url);
        RequestHandler::sendRequest($method, $url, $headers, $body);
        $postRequestCallable = $this->postRequestCallable;
        $postRequestCallable(RequestHandler::getResponseBody(), RequestHandler::getHeaders());

        $this->resetState();

        if (self::$setFailStates) {
            FailureContext::addState('url', RequestHandler::getUri() .'::'. RequestHandler::getStatusCode());
            FailureContext::addState('method', RequestHandler::getMethod());
            FailureContext::addState('request headers', $this->deepImplode(': ', RequestHandler::getRequestHeaders()));
            FailureContext::addState('request body', RequestHandler::getRequestBody());
            FailureContext::addState('response headers', $this->deepImplode(': ', RequestHandler::getHeaders()));
            FailureContext::addState('response body', RequestHandler::getResponseBody() . PHP_EOL);
        }

        if (self::$sampleRequestFormat) {
            $this->handleSampleRequest(
                self::$sampleRequestFormat,
                $method,
                $headers,
                $body,
                $url,
                RequestHandler::getBaseUrl()
            );
        }
    }

    /**
     * @Then I expect a :statusCode status code
     * @param mixed $statusCode
     */
    public function validateStatusCode($statusCode): void
    {
        $statusCode = intval($statusCode);
        Assert::assertSame(
            $statusCode,
            RequestHandler::getStatusCode(),
            sprintf('Expected status code %d but got %d', $statusCode, RequestHandler::getStatusCode())
        );
    }

    /**
     * @Then I should see the response
     */
    public function seeTheResponse(): void
    {
        echo RequestHandler::getResponseBody();
    }

    /**
     * @Then the response should be empty
     */
    public function theResponseShouldBeEmpty(): void
    {
        $actualResponse = RequestHandler::getResponseBody();

        Assert::assertEquals(null, $actualResponse, 'Expected the response to be empty');
    }

    /**
     * @Then I expect a :statusCode :apiSpec response expecting:
     * @Then I expect a :statusCode :apiSpec response
     * @param mixed $statusCode
     */
    public function validateResponse($statusCode, string $apiSpec, PyStringNode $expectedResponse = null): void
    {
        $statusCode = (int) $statusCode;
        $apiSpec = EndpointProvider::getApiSpecEndpointClass($apiSpec);

        if (!(in_array(EndPoint::class, class_implements($apiSpec)))) {
            throw new Exception('Not an apiSpec class: ' . $apiSec);
        }

        if (!method_exists($apiSpec, 'getResponseSchema')) {
            echo sprintf('Scaffolding schema for endpoint: %s...', $apiSpec);
            if (!$this->schemaExists($apiSpec)) {
                $this->addSchema(
                    $apiSpec,
                    SchemaGenerator::scaffoldHeaderSchema(RequestHandler::getHeaders()),
                    SchemaGenerator::scaffoldSchema(RequestHandler::getResponseBody())
                )
                ->addQueryParam($apiSpec, SchemaGenerator::scaffoldQueryParams(RequestHandler::getUri()));
            }
        } else {
            $schema = new Schema($apiSpec::getResponseSchema());
            if (!$schema->hasSchema(RequestHandler::getMethod(), RequestHandler::getStatusCode())) {
                echo SchemaGenerator::suggestSchema(
                    RequestHandler::getMethod(),
                    SchemaGenerator::scaffoldSchema(RequestHandler::getResponseBody()),
                    SchemaGenerator::scaffoldHeaderSchema(RequestHandler::getHeaders()),
                    RequestHandler::getStatusCode()
                );
                throw new Exception(sprintf('Schema for status code %s not defined...', RequestHandler::getStatusCode()));
            }

            $this->validateStatusCode($statusCode);
            $statusSchema = $schema->getSchema(RequestHandler::getMethod(), RequestHandler::getStatusCode());

            if (isset($statusSchema['headers'])) {
                TypeValidator::assertHeaders($statusSchema['headers'], RequestHandler::getHeaders());
            }

            try {
                $this->validate(
                    json_decode(RequestHandler::getResponseBody(), true),
                    $statusSchema['body']
                );
            } catch (Exception $e) {
                throw new Exception(sprintf(
                    'Validation failed for api spec "%s"%sRequest: "%s::%d"%sError: %s%sResponse body: %s',
                    $apiSpec,
                    PHP_EOL,
                    RequestHandler::getMethod(),
                    $statusCode,
                    PHP_EOL,
                    $e->getMessage(),
                    PHP_EOL,
                    RequestHandler::getResponseBody()
                ));
            }
        }

        if ($expectedResponse) {
            Assert::assertSame($expectedResponse->getRaw(), RequestHandler::getResponseBody());
        }
    }

    public function resetState(): void
    {
        $this->headers = [];
        $this->body = '';
    }

    private function validate($body, array $schema): void
    {
        foreach ($schema as $property => $typeDetails)
        {
            if ($property === '*') {
                foreach ($body as $index => $sut) {
                    TypeValidator::assertValue($sut, $index, $typeDetails);
                    if ($this->isLoopableType($typeDetails)) {
                        $this->validate($sut, $typeDetails['schema']);
                    }
                }
                continue;
            }

            if (empty($body)) {
                throw new Exception('No response to validate.');
            }

            if (!array_key_exists($property, $body)) {
                if (!$this->isPropertyOptional($typeDetails)) {
                    throw new RequiredPropertyMissingException($property, $body);
                }

                continue;
            }

            $sut = $body[$property];

            try {
                TypeValidator::assertValue($sut, $property, $typeDetails);
            } catch (Exception $e) {
                throw new Exception(sprintf('Validation failed for property "%s", error: %s', $property, $e->getMessage()));
            }

            $this->enforceTypeInSchema($typeDetails);

            if ($this->isLoopableType($typeDetails)) {
                $this->validate($sut, $typeDetails['schema']);
            }
        }
    }

    private function isPropertyOptional(array $typeDetails): bool
    {
        if (isset($typeDetails['optional']) && $typeDetails['optional'] === true) {
            return true;
        }

        return false;
    }

    private function enforceTypeInSchema(array $typeDetails): void
    {
        if (!array_key_exists('type', $typeDetails)) {
            throw new Exception(sprintf(
                'Invalid declaration of schema "%s::%d" "%s", each key must have a type defined.',
                RequestHandler::getMethod(),
                RequestHandler::getStatusCode(),
                print_r($typeDetails, true)
            ));
        }
    }

    private function isLoopableType(array $typeDetails): bool
    {
        if ($typeDetails['type'] == Endpoint::TYPE_OBJECT || $typeDetails['type'] === Endpoint::TYPE_ARRAY) {
            if (array_key_exists('schema', $typeDetails)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $glue
     * @param array $values
     *
     * @return string
     */
    private function deepImplode(string $glue, array $values): string
    {
        $string = '';
        foreach ($values as $name => $value) {
            $string .= $name . $glue . $value[0] . PHP_EOL;
        }

        return $string;
    }
}
