<?php

namespace Genesis\ApiSpecTests\Features\Bootstrap;

use Behat\Behat\Context\Context;
use FailAid\Context\FailureContext;
use Genesis\BehatApiSpec\Service\PlaceholderService;
use Genesis\BehatApiSpec\Service\RequestHandler;

class FeatureContext implements Context
{
    public static function setPlaceholders($body, $headers)
    {
        $body = json_decode(RequestHandler::getResponseBody(), true);

        foreach ($body as $key => $value) {
            PlaceholderService::add($key, $value);
        }

        FailureContext::addState('keywords', print_r(PlaceholderService::getAll(), true));
    }

    /**
     * @When I set the placeholder :arg1 to :arg2
     * @param mixed $arg1
     * @param mixed $arg2
     */
    public function iSetThePlaceholderTo($arg1, $arg2)
    {
        PlaceholderService::add($arg1, $arg2);
    }
}
