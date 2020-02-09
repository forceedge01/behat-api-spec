<?php

namespace Genesis\BehatApiSpec\Exception;

use Exception;

class UnknownNonScalarTypeProvided extends Exception
{
    public function __construct(string $type, string $property)
    {
        $message = sprintf('Unknown non scalar type provided %s for property %s', $type, $property);
        parent::__construct($message);
    }
}
