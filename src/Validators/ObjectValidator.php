<?php

namespace Genesis\BehatApiSpec\Validators;

use Genesis\BehatApiSpec\Contracts\Validator;
use PHPUnit\Framework\Assert;

class ObjectValidator implements Validator
{
    public static function validate($value, array $details): void
    {
        Assert::assertIsArray($value);
        Assert::assertIsString(key($value));
    }
}
