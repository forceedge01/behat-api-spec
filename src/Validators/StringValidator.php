<?php

namespace Genesis\BehatApiSpec\Validators;

use Genesis\BehatApiSpec\Contracts\Validator;
use Genesis\BehatApiSpec\Exception\RegularExpressionMismatchException;
use PHPUnit\Framework\Assert;

class StringValidator implements Validator
{
    public static function validate($value, array $details): void
    {
        Assert::assertIsString($value);

        if (isset($details['pattern'])) {
            try {
                Assert::assertRegExp($details['pattern'], $value);
            } catch (\Exception $e) {
                throw new RegularExpressionMismatchException($value, $details['pattern']);
            }
        }

        if (isset($details['value'])) {
            Assert::assertSame($details['value'], $value);
        }
    }
}
