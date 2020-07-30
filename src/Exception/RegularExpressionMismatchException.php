<?php

namespace Genesis\BehatApiSpec\Exception;

use Exception;

class RegularExpressionMismatchException extends Exception
{
    public function __construct($value, $regex)
    {
        $content = 'Content mismatch.' . PHP_EOL;
        $content .= '+ Content' . PHP_EOL;
        $content .= '- Regex' . PHP_EOL;
        $content .= PHP_EOL;
        $content .= '+ ' . $value . PHP_EOL;
        $content .= '- ' . $regex;

        parent::__construct($content);
    }
}
