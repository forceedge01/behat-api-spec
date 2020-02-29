<?php

namespace Genesis\BehatApiSpec\Service;

use Psr\Http\Message\UriInterface;

class SchemaGenerator
{
    public static function scaffoldHeaderSchema(array $headers): array
    {
        $formattedheaders = [];

        foreach ($headers as $header => $value) {
            $formattedheaders[$header] = [
                'type' => gettype($value[0]),
                'value' => $value[0],
            ];
        }

        return $formattedheaders;
    }

    public static function createQueryStringDeclarationFunction(array $queryParams): string
    {
        $queryFunction = StringBuilder::newInstance()
            ->newLine()
            ->addLine('public static function getRequestSchema(): array')
            ->addLine('{')
            ->incrementTabLevel()
            ->addLine('return [')
            ->incrementTabLevel()
            ->addLine("'queryParams' => [")
            ->incrementTabLevel();

        foreach ($queryParams as $param => $value) {
            $queryFunction
                ->addLine(sprintf("'%s' => [", $param))
                ->incrementTabLevel()
                ->addLine(sprintf("'type' => %s,", self::getTypeAsConstant(gettype($value))))
                ->addLine("'description' => '',")
                ->addLine("'example' => '$value',")
                ->addLine("'pattern' => '',")
                ->decrementTabLevel()
                ->addLine('],');
        }

        $queryFunction
            ->decrementTabLevel()
            ->addLine(']')
            ->decrementTabLevel()
            ->addLine('];')
            ->decrementTabLevel()
            ->addLine('}');

        return $queryFunction->getString();
    }

    private static function addLine(string $line, $tab): string
    {
        return PHP_EOL . self::tab($tab) . $line;
    }

    public static function scaffoldQueryParams(UriInterface $query): array
    {
        $output = [];
        parse_str($query->getQuery(), $output);

        return $output;
    }

    public static function scaffoldSchema(string $body): array
    {
        $response = json_decode($body, true);

        foreach ($response as $property => $value) {
            $schema[$property] = ['type' => gettype($value)];
            switch (gettype($value)) {
                case 'array':
                    if (is_string(key($value))) {
                        $schema[$property]['type'] = 'object';
                    }
                    $schema[$property]['schema'] = self::scaffoldSchema(json_encode($value));
                    break;
            }
        }

        return $schema;
    }

    public static function createSchemaHandlerFunction(array $details): string
    {
        $queryFunction = StringBuilder::newInstance()
            ->addLine('public static function getResponseSchema(): array')
            ->addLine('{')
            ->incrementTabLevel()
            ->addLine('return [')
            ->incrementTabLevel();

        foreach ($details as $method => $statusDetails) {
            $queryFunction->addLine(sprintf('\'%s\' => [', $method))->incrementTabLevel();
            foreach ($statusDetails as $statusCode => $unused) {
                $queryFunction->addLine(sprintf(
                    '%d => self::get%sResponseSchema(),',
                    $statusCode,
                    $statusCode . $method
                ));
            }
            $queryFunction->decrementTabLevel()->addLine('],');
        }

        $queryFunction->decrementTabLevel()
            ->addLine('];')
            ->decrementTabLevel()
            ->addLine('}');

        return $queryFunction->getString();
    }

    public static function suggestSchema(string $method, array $schema, array $headers, int $statusCode): string
    {
        $queryFunction = StringBuilder::newInstance()
            ->newLine()
            ->addLine('public static function get' . $statusCode . $method . 'ResponseSchema(): array')
            ->addLine('{')
            ->incrementTabLevel()->addLine('return [')
            ->incrementTabLevel()->addLine("'headers' => [")
            ->setTabLevel(0)->addLine(trim(self::getSchemaHeaderPropertiesAsString($headers, 4), PHP_EOL))->setTabLevel(3)
            ->addLine('],')
            ->addLine("'body' => [")
            ->setTabLevel(0)->addLine(trim(self::getSchemaPropertiesAsString($schema, 4), PHP_EOL))->setTabLevel(3)
            ->addLine('],')
            ->decrementTabLevel()->addLine('];')
            ->decrementTabLevel()->addLine('}');

        return $queryFunction->getString();
    }

    public static function appendSchemaToEndpointSpec(string $apiSpec, string $schema): void
    {
        $file = EndpointProvider::getFilename($apiSpec);
        $contents = file_get_contents($file);
        $contents = preg_replace('/(.*)}/su', '${1}' . PHP_EOL . $schema . PHP_EOL . '}', $contents);

        file_put_contents($file, $contents);
    }

    private static function tab($count): string
    {
        return str_repeat(' ', $count*4);
    }

    private static function getSchemaHeaderPropertiesAsString(array $headers, int $tab): string
    {
        $getSchemaMethod = '';
        foreach ($headers as $header => $value) {
            $getSchemaMethod .= self::tab($tab) . "'$header' => [" . PHP_EOL;
            $getSchemaMethod .= self::tab($tab+1) . "'value' => '{$value['value']}'," . PHP_EOL;
            $getSchemaMethod .= self::tab($tab+1) . sprintf("'type' => %s,", self::getTypeAsConstant($value['type'])) . PHP_EOL;
            $getSchemaMethod .= self::tab($tab) . '],' . PHP_EOL;
        }

        return $getSchemaMethod;
    }

    private static function getTypeAsConstant($type): string
    {
        return sprintf('self::TYPE_%s', strtoupper($type));
    }

    private static function getSchemaPropertiesAsString(array $schema, int $tab): string
    {
        $getSchemaMethod = '';
        foreach ($schema as $property => $value) {
            if ($value['type'] === 'object' || $value['type'] === 'array') {
                $getSchemaMethod .= self::tab($tab) . "'$property' => [" . PHP_EOL;
                $getSchemaMethod .= self::tab($tab+1) . sprintf("'type' => self::TYPE_%s,", strtoupper($value['type'])) . PHP_EOL;
                $getSchemaMethod .= self::tab($tab+1) . "'optional' => false," . PHP_EOL;
                $getSchemaMethod .= self::tab($tab+1) . "'schema' => [" . PHP_EOL;
                $getSchemaMethod .= self::getSchemaPropertiesAsString($value['schema'], $tab+2);
                $getSchemaMethod .= self::tab($tab+1) . '],' . PHP_EOL;
                $getSchemaMethod .= self::tab($tab) . '],' . PHP_EOL;
            } else {
                $getSchemaMethod .= self::tab($tab) . "'$property' => [" . PHP_EOL;
                $getSchemaMethod .= self::tab($tab+1) . sprintf("'type' => self::TYPE_%s,", strtoupper($value['type'])) . PHP_EOL;
                $getSchemaMethod .= self::tab($tab+1) . "'optional' => false," . PHP_EOL;

                switch ($value['type']) {
                    case 'string':
                        $getSchemaMethod .= self::tab($tab+1) . "'pattern' => null," . PHP_EOL;
                        break;
                    case 'integer':
                        $getSchemaMethod .= self::tab($tab+1) . "'min' => null," . PHP_EOL;
                        $getSchemaMethod .= self::tab($tab+1) . "'max' => null," . PHP_EOL;
                        break;
                }

                $getSchemaMethod .= self::tab($tab) . '],' . PHP_EOL;
            }
        }

        return $getSchemaMethod;
    }
}
