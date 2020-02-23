<?php

namespace Genesis\BehatApiSpec\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Exception;
use Genesis\BehatApiSpec\Contracts\Endpoint;
use Genesis\BehatApiSpec\Exception\RequiredPropertyMissingException;
use Genesis\BehatApiSpec\Service\RequestHandler;
use Genesis\BehatApiSpec\Service\SchemaGenerator;
use Genesis\BehatApiSpec\Service\TypeValidator;
use Genesis\BehatApiSpec\Validators;
use Genesis\BehatApiSpec\Validators\StringValidator;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Assert;

class ApiSpecContext implements Context
{
    private static $mappings;

    private static $schemas;

    private static $currentScenario;

    private static $validSnapshots;

    private static $updateSnapshots;

    private $headers = [];

    public static function setSpecOptions(string $baseUrl, array $mappings): void
    {
        RequestHandler::setBaseUrl($baseUrl);
        self::$mappings = $mappings;
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
     * @AfterSuite
     */
    public static function generateSchema()
    {
        if (self::$schemas) {
            echo 'Appending to schema files:' . PHP_EOL;
            $schemaString = '';
            foreach (self::$schemas as $apiSpec => $details) {
                $schemaString = SchemaGenerator::createSchemaHandlerFunction($details);
                echo $apiSpec . PHP_EOL;
                foreach ($details as $statusCode => $schema) {
                    echo $statusCode . ' ';
                    $schemaString .= PHP_EOL . PHP_EOL;
                    $schemaString .= SchemaGenerator::suggestSchema($schema['body'], $schema['headers'], $statusCode);
                }
                SchemaGenerator::appendSchemaToEndpointSpec($apiSpec, $schemaString);
                echo PHP_EOL . PHP_EOL;
            }
        }
    }

    /**
     * @AfterScenario
     */
    public function storeObsoleteFiles()
    {
        self::$validSnapshots[] = self::getSnapshotName();
    }

    /**
     * @AfterSuite
     */
    public static function displayObsoleteFiles()
    {
        // Filter out directories to check.
        $directories = [];
        foreach (self::$validSnapshots as $snapshot) {
            $directories[dirname($snapshot)][] = basename($snapshot);
        }
        // Go through each directory and check for files that don't exist.
        $obsoleteFiles = [];
        foreach ($directories as $directory => $files) {
            $scannedFiles = scandir($directory);
            foreach ($scannedFiles as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                if (!in_array($directory . DIRECTORY_SEPARATOR . $file, self::$validSnapshots)) {
                    $obsoleteFiles[] = $directory . DIRECTORY_SEPARATOR . $file;
                }
            }
        }

        if ($obsoleteFiles) {
            echo 'Obsolete files:' . PHP_EOL;
            print_r($obsoleteFiles);

            if (self::$updateSnapshots) {
                echo 'Deleting obsolete files...';
                foreach ($obsoleteFiles as $file) {
                    unlink($file);
                }
            }
        }
    }

    public static function setUpdateSnapshots(bool $bool)
    {
        self::$updateSnapshots = $bool;
    }

    /**
     * @BeforeScenario
     * @param mixed $scope
     */
    public function setCurrentScenario($scope)
    {
        self::$currentScenario = $scope;
    }

    /**
     * @Then I expect a :statusCode :apiSpec response expecting:
     * @Then I expect a :statusCode :apiSpec response
     * @param mixed $statusCode
     * @param mixed $apiSpec
     */
    public function validateResponse($statusCode, $apiSpec, PyStringNode $expectedResponse = null): void
    {
        $statusCode = (int) $statusCode;
        $apiSpec = $this->getApiSpecEndpointClass($apiSpec);

        if (!(in_array(EndPoint::class, class_implements($apiSpec)))) {
            throw new Exception('Not an apiSpec class: ' . $apiSec);
        }

        if (!method_exists($apiSpec, 'getSchema')) {
            echo sprintf('Scaffolding schema for endpoint: %s...', $apiSpec);
            if (!isset(self::$schemas[$apiSpec][RequestHandler::getStatusCode()])) {
                $schema['body'] = SchemaGenerator::scaffoldSchema(RequestHandler::getResponseBody());
                $schema['headers'] = SchemaGenerator::scaffoldHeaderSchema(RequestHandler::getHeaders());
                self::$schemas[$apiSpec][RequestHandler::getStatusCode()] = $schema;
            }
        } else {
            $schema = $apiSpec::getSchema();
            if (!isset($schema[RequestHandler::getStatusCode()])) {
                echo sprintf('WARNING: Schema for status code %s not defined...', RequestHandler::getStatusCode());
            } else {
                Assert::assertSame(
                    $statusCode,
                    RequestHandler::getStatusCode(),
                    sprintf('Expected status code %d but got %d', $statusCode, RequestHandler::getStatusCode())
                );

                $statusSchema = $schema[RequestHandler::getStatusCode()];

                if (isset($statusSchema['headers'])) {
                    TypeValidator::assertHeaders($statusSchema['headers'], RequestHandler::getHeaders());
                }

                $this->validate(
                    json_decode(RequestHandler::getResponseBody(), true),
                    $statusSchema['body']
                );
            }
        }

        if ($expectedResponse) {
            Assert::assertSame($expectedResponse->getRaw(), RequestHandler::getResponseBody());
        }
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
     * @When I make a :method request to :arg1 endpoint with body:
     * @When I make a :method request to :arg1 endpoint with query string :queryString
     * @param mixed $method
     * @param mixed $endpoint
     */
    public function sendRequest($method, $endpoint, PyStringNode $body = null, string $queryString = null): void
    {
        $endpoint = $this->getApiSpecEndpointClass($endpoint);
        $headers = array_merge($this->headers, $endpoint::getHeaders());
        $url = $endpoint::getEndpoint() . ($queryString ? '?' . $queryString : '');
        RequestHandler::sendRequest($method, $url, $headers, (string) $body);
        $this->resetState();
    }

    /**
     * @Then the response should match the snapshot
     * @param null|mixed $uniqueName
     */
    public function theResponseShouldMatchTheSnapshot()
    {
        $uniqueName = self::getSnapshotName();
        $actualResponse = RequestHandler::getResponseBody();
        if (!is_dir(dirname($uniqueName))) {
            mkdir(dirname($uniqueName), 0777, true);
        }

        if (file_exists($uniqueName)) {
            $expected = file_get_contents($uniqueName);
            try {
                StringValidator::validate($actualResponse, ['value' => $expected]);
            } catch (Exception $e) {
                if (! self::$updateSnapshots) {
                    throw $e;
                }

                echo 'Updating snapshot: ' . $uniqueName;
                file_put_contents($uniqueName, $actualResponse);
                $this->theResponseShouldMatchTheSnapshot();
            }
        } else {
            echo 'Generating screenshot:' . $uniqueName;
            file_put_contents($uniqueName, $actualResponse);
        }
    }

    private static function getSnapshotName(): string
    {
        $title = self::$currentScenario->getScenario()->getTitle();
        if (!$title) {
            throw new Exception('In order to create a snapshot, please declare scenario title.');
        }

        $featurePath = self::$currentScenario->getFeature()->getFile();

        return substr($featurePath, 0, strrpos($featurePath, '/'))
            . DIRECTORY_SEPARATOR
            . '__snapshots__'
            . DIRECTORY_SEPARATOR
            . strtolower(str_replace([' '], '-', $title))
            . '.txt';
    }

    public function resetState()
    {
        $this->headers = [];
    }

    private function validate($body, array $schema): void
    {
        foreach ($schema as $property => $typeDetails)
        {
            if ($property === '*') {
                foreach ($body as $index => $sut) {
                    TypeValidator::assertValue($sut, $index, $typeDetails);
                }
                continue;
            }

            $sut = $body[$property] ?? null;

            if (!$sut) {
                if (!isset($typeDetails['optional']) || $typeDetails['optional'] !== true) {
                    throw new RequiredPropertyMissingException($property);
                }

                continue;
            }

            try {
                TypeValidator::assertValue($sut, $property, $typeDetails);
            } catch (Exception $e) {
                throw new Exception(sprintf('Validation failed for property %s, error: %s', $property, $e->getMessage()));
            }
            if ($typeDetails['type'] == Endpoint::TYPE_OBJECT || $typeDetails['type'] === Endpoint::TYPE_ARRAY) {
                $this->validate($sut, $typeDetails['schema']);
            }
        }
    }

    private function getApiSpecEndpointClass(string $class): string
    {
        return self::$mappings['endpoint'] . $class;
    }
}

