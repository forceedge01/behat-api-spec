<?php

namespace Genesis\BehatApiSpec\Service;

class Output
{
    /**
     * @param string $expected
     * @param string $actual
     * @param string $message
     *
     * @return string
     */
    public static function provideDiff($expected, $actual, $message = null)
    {
        return 'Mismatch: (- expected, + actual)' . PHP_EOL . PHP_EOL .
            '- ' . $expected . PHP_EOL .
            '+ ' . $actual . PHP_EOL . PHP_EOL .
            'Info: ' . $message;
    }
}
