<?php

namespace Genesis\BehatApiSpec\Validators;

use Genesis\BehatApiSpec\Contracts\Validator;
use PHPUnit\Framework\Assert;

class EnumValidator implements Validator
{
    public static function validate($value, array $details): void
    {
        Assert::assertTrue(
            in_array($value, $details['enum']),
            sprintf('%s value not allowed, allowed values are: ', $value, print_r($details['enum'], true))
        );
    }
}
