<?php

namespace Genesis\BehatApiSpec\Traits;

use Genesis\BehatApiSpec\Service\RequestHandler;
use Genesis\BehatApiSpec\Service\SchemaGenerator;

trait SchemaGeneratorTrait
{
    private static $schemas = [];

    private static $queryParams = [];

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
        self::$queryParams[$apiSpec] = [];
        if ($params) {
            self::$queryParams[$apiSpec] = array_merge($params, self::$queryParams[$apiSpec]);
        }

        return $this;
    }
}
