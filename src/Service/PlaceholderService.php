<?php

namespace Genesis\BehatApiSpec\Service;

class PlaceholderService
{
    private static $placeholders = [];

    public static function reset()
    {
        self::$placeholders = [];
    }

    /**
     * If the string contains placeholders in the format of {{name_of_placeholder}}, resolve these values.
     * If placeholder is not set, placeholder is left unchanged. If value found is not scalar, exception is thrown.
     * 
     * @param string $string
     * @param callable $closure A custom closure method to manipulate the replaced value even further
     *
     * @return string
     */
    public function resolveInString(string $string, callable $closure = null): string
    {
        $matches = [];

        preg_match_all('/({{.+?}})/', $string, $matches);
        $func = $closure ? $closure : 'str_replace';

        if (isset($matches[0])) {
            foreach ($matches[0] as $match) {
                $placeholder = trim($match, '{}');
                try {
                    $value = self::getValue($placeholder);
                } catch (\Exception $e) {
                    continue;
                }

                if (!is_scalar($value)) {
                    throw new Exception(sprintf(
                        'Placeholder "%s" value is not scalar and cannot be resolved in string - value: %s',
                        $placeholder,
                        print_r($value, true)
                    ));
                }
                $string = $func($match, $value, $string);
            }
        }

        return $string;
    }

    public static function add(string $name, $value)
    {
        self::$placeholders[$name] = $value;
    }

    public static function getValue(string $name)
    {
        if (isset(self::$placeholders[$name])) {
            return self::$placeholders[$name];
        }

        throw new \Exception(sprintf('Placeholder %s value not found', $name));
    }

    public static function getAll(): array
    {
        return self::$placeholders;
    }
}
