<?php

namespace Genesis\BehatApiSpec\Validators;

use Genesis\BehatApiSpec\Contracts\Validator;

class CallbackValidator implements Validator
{
    public static function validate($value, array $details): void
    {
        $details['callback']($value);
    }
}
