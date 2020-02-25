<?php

namespace Genesis\BehatApiSpec\Validators;

use Genesis\BehatApiSpec\Contracts\Validator;
use PHPUnit\Framework\Assert;

class ObjectValidator implements Validator
{
    public static function validate($value, array $details): void
    {
        Assert::assertIsArray($value);
        Assert::assertIsString(
            key($value),
            sprintf('Failed asserting that key "%s" is string, update schema to TYPE_ARRAY for json array.', key($value))
        );
    }
}
