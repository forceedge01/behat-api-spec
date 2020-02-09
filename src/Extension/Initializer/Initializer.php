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
    /**
     * @var array
     */
    private $specMappings = [];


    public function __construct(array $specMappings = [])
    {
        $this->specMappings = $specMappings;
    }


    public function initializeContext(Context $context)
    {
        if ($context instanceof ApiSpecContext) {
            $context::setSpecMappings($this->specMappings);
            $context::registerInternalTypes();
        }
    }
}
