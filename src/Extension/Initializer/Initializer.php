<?php

namespace Genesis\BehatApiSpec\Extension\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use Genesis\BehatApiSpec\Context\ApiSpecContext;

/**
 * ContextInitialiser class.
 */
class Initializer implements ContextInitializer
{
    private $specMappings = [];

    private $baseUrl;

    public function __construct(string $baseUrl, array $specMappings = [])
    {
        $this->specMappings = $specMappings;
        $this->baseUrl = $baseUrl;
    }

    public function initializeContext(Context $context)
    {
        if ($context instanceof ApiSpecContext) {
            $context::setSpecOptions($this->baseUrl, $this->specMappings);
            $context::registerInternalTypes();
        }
    }
}
