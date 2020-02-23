<?php

namespace Genesis\BehatApiSpec\Exception;

use Exception;

class RequiredPropertyMissingException extends Exception
{
    public function __construct($property, $body)
    {
        $message = sprintf('Missing required value "%s" from schema, response: %s', $property, print_r($body, true));
        parent::__construct($message);
    }
}
