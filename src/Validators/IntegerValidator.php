<?php

namespace Genesis\BehatApiSpec\Validators;

use Genesis\BehatApiSpec\Contracts\Validator;
use PHPUnit\Framework\Assert;

class IntegerValidator implements Validator
{
    public static function validate($value, array $details): void
    {
        Assert::assertIsInt($value);

        if (isset($details['min'])) {
            Assert::assertGreaterThan($details['min'], $value);
        }

        if (isset($details['max'])) {
            Assert::assertLessThan($details['min'], $value);
        }
    }
}
