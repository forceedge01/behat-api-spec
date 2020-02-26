<?php

namespace Genesis\BehatApiSpec\Traits;

use Behat\Gherkin\Node\PyStringNode;
use Exception;
use Genesis\BehatApiSpec\Exception\KeyNotFoundException;
use Genesis\BehatApiSpec\Service\RequestHandler;
use PHPUnit\Framework\Assert;

trait JsonValidateTrait
{
    /**
     * @Then I expect the following JSON response:
     */
    public function shouldlHaveTheContent(PyStringNode $expectedResponse)
    {
        Assert::assertSame(RequestHandler::getResponseBody(), (string) $expectedResponse);
    }

    /**
     * @Then I expect the following content in the :format response :key key:
     */
    public function shouldHaveTheFollowingInTheResponse(string $format, string $key, PyStringNode $response)
    {
        switch (strtolower($format)) {
            case 'json':
                $this->validateJson($key, $response);
                break;

            default:
                throw new Exception('Unsupported format: ' . $format);
        }
    }

    private function validateJson(string $key, PyStringNode $expectedResponse)
    {
        $json = RequestHandler::getResponseBody();
        $body = json_decode($json, true);
        $keys = explode('.', $key);

        $sut = $body;
        foreach ($keys as $property) {
            if (isset($sut[$property])) {
                $sut = $sut[$property];
                continue;
            }

            throw new KeyNotFoundException(sprintf(
                'Property "%s" not found when traversing through body for key "%s" in response: "%s"',
                $property,
                $key,
                $json
            ));
        }

        Assert::assertSame(json_encode($sut), (string) $expectedResponse);
    }
}
