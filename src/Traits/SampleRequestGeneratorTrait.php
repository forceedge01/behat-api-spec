<?php

namespace Genesis\BehatApiSpec\Traits;

use Exception;
use Genesis\BehatApiSpec\Service\RequestHandler;

trait SampleRequestGeneratorTrait
{
    private static $sampleRequestFormat;

    public static function setSampleRequest(string $format)
    {
        self::$sampleRequestFormat = $format;
    }

    public function handleSampleRequest(string $format, string $method, array $headers, string $body, string $url)
    {
        switch (self::$sampleRequestFormat) {
            case 'curl':
                $command = 'curl';
                $command .= " -X $method";
                if ($headers) {
                    foreach ($headers as $header => $value) {
                        $command .= " --header '$header: $value'";
                    }
                }
                if ($body) {
                    $command .= ' -d \'' . $body . '\'';
                }
                $command .= " '" . RequestHandler::getBaseUrl() . $url . "'";
                echo $command;
                break;

            default:
                throw new Exception('Unknown format for sample request: ' . self::$sampleRequestFormat);
        }
    }
}
