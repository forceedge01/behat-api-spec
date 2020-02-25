<?php

namespace Genesis\BehatApiSpec\Validators;

use Genesis\BehatApiSpec\Contracts\Validator;
use PHPUnit\Framework\Assert;

class ArrayValidator implements Validator
{
    public static function validate($value, array $details): void
    {
        Assert::assertIsArray($value);
        Assert::assertIsInt(
            key($value),
            sprintf('Failed asserting that key "%s" is int, update schema to TYPE_OBJECT for json object.', key($value))
        );
    }
}
