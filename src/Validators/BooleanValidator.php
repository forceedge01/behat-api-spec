<?php

namespace Genesis\BehatApiSpec\Validators;

use Genesis\BehatApiSpec\Contracts\Validator;
use PHPUnit\Framework\Assert;

class BooleanValidator implements Validator
{
    public static function validate($value, array $details): void
    {
        Assert::assertIsBool($value);
    }
}
