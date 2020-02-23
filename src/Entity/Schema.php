<?php

namespace Genesis\BehatApiSpec\Entity;

class Schema
{
    private $schema;

    public function __construct(array $schema)
    {
        $this->schema = $schema;
    }

    public function getSchema(string $method, int $statusCode): ?array
    {
        if (isset($this->schema[$method][$statusCode])) {
            return $this->schema[$method][$statusCode];
        }

        return null;
    }

    public function hasSchema(string $method, int $statusCode): bool
    {
        if (is_array($this->getSchema($method, $statusCode))) {
            return true;
        }

        return false;
    }
}
