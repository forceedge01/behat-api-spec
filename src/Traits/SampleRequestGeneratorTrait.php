<?php

namespace Genesis\BehatApiSpec\Traits;

use Exception;

trait SampleRequestGeneratorTrait
{
    private static $sampleRequestFormat;

    public static function setSampleRequest(string $format)
    {
        self::$sampleRequestFormat = $format;
    }

    public function handleSampleRequest(string $format, string $method, array $headers, string $body, string $url, string $baseUrl)
    {
        switch (self::$sampleRequestFormat) {
            case 'curl':
                echo $this->getSampleCurlRequest($method, $headers, $body, $url, $baseUrl);
                break;

            default:
                throw new Exception('Unknown format for sample request: ' . self::$sampleRequestFormat);
        }
    }

    private function getSampleCurlRequest(
        string $method,
        array $headers,
        string $body,
        string $url,
        string $baseUrl
    ): string {
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
        $command .= " '" . $baseUrl . $url . "'";

        return $command;
    }
}
