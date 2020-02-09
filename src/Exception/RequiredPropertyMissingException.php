<?php

namespace Genesis\BehatApiSpec\Exception;

use Exception;

class RequiredPropertyMissingException extends Exception
{
    public function __construct($property)
    {
        $message = sprintf('Missing required value "%s" from schema', $property);
        parent::__construct($message);
    }
}
