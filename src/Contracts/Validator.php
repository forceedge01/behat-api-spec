<?php

namespace Genesis\BehatApiSpec\Contracts;

interface Validator
{
    public static function validate($value, array $rules): void;
}
