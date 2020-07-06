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

    private $options = [];

    public function __construct(string $baseUrl, array $specMappings = [], array $options = ['stripSpaces' => false])
    {
        $this->specMappings = $specMappings;
        $this->baseUrl = $baseUrl;
        $this->options = $options;
    }

    public function getSpecMappings(): array
    {
        return $this->specMappings;
    }

    public function initializeContext(Context $context)
    {
        if (is_a($context, 'FailAid\\Context\\FailureContext')) {
            ApiSpecContext::setFailStates(true);
        }

        if ($context instanceof ApiSpecContext) {
            ApiSpecContext::setSpecOptions($this->baseUrl, $this->specMappings, $this->options);
            ApiSpecContext::registerInternalTypes();
        }
    }
}
