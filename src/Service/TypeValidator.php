<?php

namespace Genesis\BehatApiSpec\Service;

use Exception;
use Genesis\BehatApiSpec\Exception\UnknownScalarTypeProvided;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;

class TypeValidator
{
    private static $typeValidators = [];

    public static function assertValue($value, string $property, array $typeDetails)
    {
        if (!array_key_exists('type', $typeDetails)) {
            throw new Exception('Invalid schema declaration, each key must have a type defined.');
        }

        $validator = self::getValidator($typeDetails['type']);

        if (!$validator) {
            throw new UnknownScalarTypeProvided($property, $typeDetails['type']);
        }

        try {
            $validator::validate($value, $typeDetails);
        } catch (Exception $e) {
            $message = $e->getMessage();
            if ($e instanceof ExpectationFailedException && $e->getComparisonFailure()) {
                $message .= PHP_EOL . $e->getComparisonFailure()->getDiff();
            }

            throw new Exception(sprintf('Validator "%s" failed, error: %s', $validator, $message));
        }
    }

    public static function assertHeaders(array $expectedHeaders, array $actualHeaders): void
    {
        foreach ($expectedHeaders as $expectedHeader => $headerDetail) {
            Assert::arrayHasKey(
                $expectedHeader,
                $actualHeaders,
                sprintf('Validation failed for header %s, error: Header not set.', $expectedHeader)
            );

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
