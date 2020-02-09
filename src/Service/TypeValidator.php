<?php

namespace Genesis\BehatApiSpec\Service;

use Exception;
use Genesis\BehatApiSpec\Exception\UnknownScalarTypeProvided;

class TypeValidator
{
    private static $typeValidators = [];

    public static function assertValue($value, string $property, array $typeDetails)
    {
        $validator = self::getValidator($typeDetails['type']);

        if (!$validator) {
            throw new UnknownScalarTypeProvided($property, $typeDetails['type']);
        }

        $validator::validate($value, $typeDetails);
    }

    public static function assertHeaders(array $expectedHeaders, array $actualHeaders): void
    {
        foreach ($expectedHeaders as $expectedHeader => $headerDetail) {
            try {
                self::assertValue($actualHeaders[$expectedHeader][0], $expectedHeader, $headerDetail);
            } catch (Exception $e) {
                throw new Exception(sprintf('Validation failed for header %s, error: %s', $expectedHeader, $e->getMessage()));
            }
        }
    }

    public static function registerValidator(string $type, string $class)
    {
        self::$typeValidators[$type] = $class;
    }

    private static function getValidator(string $type): ?string
    {
        return self::$typeValidators[$type] ?? null;
    }
}
