<?php

namespace Genesis\BehatApiSpec\Validators;

use Genesis\BehatApiSpec\Contracts\Validator;
use PHPUnit\Framework\Assert;

class NumberValidator implements Validator
{
    public static function validate($value, array $details): void
    {
        Assert::assertIsNumeric($value);

        if (isset($details['pattern'])) {
            Assert::assertRegExp($details['pattern'], $value);
        }
    }
}
