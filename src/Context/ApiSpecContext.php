<?php

namespace Genesis\BehatApiSpec\Context;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Exception;
use Genesis\BehatApiSpec\Contracts\Endpoint;
use Genesis\BehatApiSpec\Entity\Schema;
use Genesis\BehatApiSpec\Exception\RequiredPropertyMissingException;
use Genesis\BehatApiSpec\Service\RequestHandler;
use Genesis\BehatApiSpec\Service\SchemaGenerator;
use Genesis\BehatApiSpec\Service\Snapshot;
use Genesis\BehatApiSpec\Service\TypeValidator;
use Genesis\BehatApiSpec\Validators;
use Genesis\BehatApiSpec\Validators\StringValidator;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\Assert;

class ApiSpecContext implements Context
{
    private static $mappings;

    private static $schemas = [];

    private static $queryParams = [];

    private static $currentScenario;

    private static $validSnapshots;

    private static $updateSnapshots;

    private static $sampleRequestFormat;

    private static $updatedSnapshots = 0;

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
                $schemaString .= SchemaGenerator::createQueryStringDeclarationFunction(self::$queryParams[$apiSpec]);
                foreach ($details as $method => $statusDetails) {
                    echo PHP_EOL . $apiSpec . PHP_EOL;
                    foreach ($statusDetails as $statusCode => $schema) {
                        echo $method . '::' . $statusCode . ' ';
                        $schemaString .= SchemaGenerator::suggestSchema(
                            $method,
                            $schema['body'],
                            $schema['headers'],
                            $statusCode
                        );
                    }
                }

                SchemaGenerator::appendSchemaToEndpointSpec($apiSpec, trim($schemaString, PHP_EOL));
                echo PHP_EOL . PHP_EOL;
            }
        }
    }

    /**
     * @AfterScenario
     */
    public function storeObsoleteFiles()
    {
        self::$validSnapshots[Snapshot::getSnapshotPath(self::$currentScenario)][] = Snapshot::getSnapshotTitle(self::$currentScenario);
    }

    /**
     * @AfterSuite
     */
    public static function displayObsoleteFiles()
    {
        // Go through each directory and check for files that don't exist.
        $obsoleteFiles = [];
        $obsoleteSnapshots = [];
        foreach (self::$validSnapshots as $snapshotFile => $snapshots) {
            $directory = dirname($snapshotFile);
            $scannedFiles = scandir($directory);
            foreach ($scannedFiles as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                if (!in_array($snapshotFile, array_keys(self::$validSnapshots))) {
                    $obsoleteFiles[] = snapshotFile;
                    continue;
                }
            }

            $storedSnapshots = Snapshot::getSnapshots($snapshotFile);
            foreach ($storedSnapshots as $scenario => $storedSnapshot) {
                if (!in_array($scenario, $snapshots)) {
                    $obsoleteSnapshots[$snapshotFile][] = $scenario;
                }
            }
        }

        if ($obsoleteFiles || $obsoleteSnapshots) {
            echo 'Obsolete files:' . PHP_EOL;
            print_r($obsoleteFiles);
            echo 'Obsolete snapshots:' . PHP_EOL;
            print_r($obsoleteSnapshots);

            if (self::$updateSnapshots) {
                echo 'Deleting obsolete files...' . PHP_EOL;
                foreach ($obsoleteFiles as $file) {
                    unlink($file);
                }

                foreach ($obsoleteSnapshots as $file => $snapshots) {
                    foreach ($snapshots as $snapshot) {
                        echo 'Removing snapshot: ' . $snapshot;
                        Snapshot::remove($file, $snapshot);
                    }
                }
            }
        }
    }

    /**
     * @AfterSuite
     */
    public static function displayUpdatedSnapshots()
    {
        if (self::$updateSnapshots) {
            echo 'Updated snapshot(s): ' . self::$updatedSnapshots;
        }
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
     * @Given I set the following headers:
     */
    public function iSetTheFollowingHeaders(TableNode $headers): void
    {
        foreach ($headers->getRowsHash() as $header => $value) {
            $this->headers[$header] = $value;
        }
    }

    /**
     * @When I make a :method request to :arg1 endpoint
     * @When I make a :method request to :arg1 endpoint with body:
     * @When I make a :method request to :arg1 endpoint with query string :queryString
     * @When I make a :method request to :arg1 endpoint with query string :queryString and body:
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

        if (self::$sampleRequestFormat) {
            switch (self::$sampleRequestFormat) {
                case 'curl':
                    $command = 'curl';
                    $command .= " -X $method";
                    if ($headers) {
                        foreach ($headers as $header => $value) {
                            $command .= " --header '$header: $value'";
                        }
                    }
                    if ($body) {
                        $command .= ' -d \'' . (string) $body . '\'';
                    }
                    $command .= " '" . RequestHandler::getBaseUrl() . $url . "'";
                    echo $command;
                    break;

                default:
                    throw new Exception('Unknown format for sample request: ' . self::$sampleRequestFormat);
            }
        }
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
            if (!$this->schemaExists($apiSpec)) {
                $this->addSchema(
                    $apiSpec,
                    SchemaGenerator::scaffoldHeaderSchema(RequestHandler::getHeaders()),
                    SchemaGenerator::scaffoldSchema(RequestHandler::getResponseBody())
                )
                ->addQueryParam($apiSpec, SchemaGenerator::scaffoldQueryParams(RequestHandler::getUri()));
            }
        } else {
            $schema = new Schema($apiSpec::getSchema());
            if (!$schema->hasSchema(RequestHandler::getMethod(), RequestHandler::getStatusCode())) {
                echo SchemaGenerator::suggestSchema(
                    RequestHandler::getMethod(),
                    SchemaGenerator::scaffoldSchema(RequestHandler::getResponseBody()),
                    SchemaGenerator::scaffoldHeaderSchema(RequestHandler::getHeaders()),
                    RequestHandler::getStatusCode()
                );
                throw new Exception(sprintf('Schema for status code %s not defined...', RequestHandler::getStatusCode()));
            }

            Assert::assertSame(
                $statusCode,
                RequestHandler::getStatusCode(),
                sprintf('Expected status code %d but got %d', $statusCode, RequestHandler::getStatusCode())
            );

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
                    'Validation failed, error: %s, response body: %s',
                    $e->getMessage(),
                    RequestHandler::getResponseBody()
                ));
            }
        }

        if ($expectedResponse) {
            Assert::assertSame($expectedResponse->getRaw(), RequestHandler::getResponseBody());
        }
    }

    /**
     * @Then the response should match the snapshot
     * @param null|mixed $uniqueName
     */
    public function theResponseShouldMatchTheSnapshot()
    {
        $title = Snapshot::getSnapshotTitle(self::$currentScenario);
        $path = Snapshot::getSnapshotPath(self::$currentScenario);
        $actualResponse = RequestHandler::getResponseBody();
        Snapshot::createSnapshotDir($path);

        if (Snapshot::exists($path, $title)) {
            $expected = Snapshot::getSnapshot($path, $title);
            try {
                StringValidator::validate($actualResponse, ['value' => $expected]);
            } catch (Exception $e) {
                if (! self::$updateSnapshots) {
                    echo 'Update snapshot with --update-snapshots or -u flag.';
                    throw $e;
                }

                echo 'Updating snapshot... ';
                Snapshot::save($path, $title, $actualResponse);
                self::$updatedSnapshots++;
            }
        } else {
            echo 'Generating snapshot: ' . $title;
            Snapshot::save($path, $title, $actualResponse);
        }
    }

    public static function setUpdateSnapshots(bool $bool)
    {
        self::$updateSnapshots = $bool;
    }

    public static function setSampleRequest(string $format)
    {
        self::$sampleRequestFormat = $format;
    }

    public function resetState()
    {
        $this->headers = [];
    }

    private function schemaExists(string $apiSpec): bool
    {
        return isset(self::$schemas[$apiSpec][RequestHandler::getMethod()][RequestHandler::getStatusCode()]);
    }

    private function addSchema(string $apiSpec, array $headers, array $body): self
    {
        self::$schemas[$apiSpec][RequestHandler::getMethod()][RequestHandler::getStatusCode()] = [
            'headers' => $headers,
            'body' => $body
        ];

        return $this;
    }

    private function addQueryParam(string $apiSpec, array $params): self
    {
        if ($params) {
            if (!isset(self::$queryParams[$apiSpec])) {
                self::$queryParams[$apiSpec] = [];
            }
            self::$queryParams[$apiSpec] = array_merge($params, self::$queryParams[$apiSpec]);
        }

        return $this;
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

            if (empty($body)) {
                throw new Exception('No response to validate.');
            }

            if (!array_key_exists($property, $body)) {
                if (!isset($typeDetails['optional']) || $typeDetails['optional'] !== true) {
                    throw new RequiredPropertyMissingException($property, $body);
                }

                continue;
            }

            $sut = $body[$property];

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

