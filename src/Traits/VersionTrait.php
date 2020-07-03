<?php

namespace Genesis\BehatApiSpec\Traits;

use Genesis\BehatApiSpec\Service\PlaceholderService;

trait VersionTrait
{
    /**
     * @Given I use version :version of the API
     *
     * Use version in your header or other configuration by calling ApiSpecContext::getVersion().
     */
    public function useVersion($version)
    {
        PlaceholderService::add('API_VERSION', $version);
    }

    /**
     * @return string
     */
    public static function getVersion()
    {
        return PlaceholderService::get('API_VERSION');
    }
}
