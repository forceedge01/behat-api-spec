<?php

namespace Genesis\BehatApiSpec\Validators;

use Genesis\BehatApiSpec\Contracts\Validator;
use PHPUnit\Framework\Assert;

class StringValidator implements Validator
{
    public static function validate($value, array $details): void
    {
        Assert::assertIsString($value);

        if (isset($details['pattern'])) {
            Assert::assertRegExp($details['pattern'], $value);
        }

        if (isset($details['value'])) {
            Assert::assertSame($details['value'], $value);
        }
    }
}
