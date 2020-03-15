<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Genesis\BehatApiSpec\Service\EndpointProvider;
use Symfony\Component\Yaml\Yaml;

// Path to the behat.yml file.
$configPath = getenv('BEHAT_CONFIG_PATH') ?: __DIR__ . '/../behat.yml';

// Profile to use the config from.
$profile = getenv('DEFAULT_PROFILE') ?: 'default';

$content = Yaml::parseFile($configPath);

if (!isset($content['default']['extensions']['Genesis\BehatApiSpec\Extension']['specMappings']['path'])) {
    die('Config not found.');
}

$path = $content['default']['extensions']['Genesis\BehatApiSpec\Extension']['specMappings']['path'];
$namespace = $content['default']['extensions']['Genesis\BehatApiSpec\Extension']['specMappings']['endpoint'];
EndpointProvider::setBaseNamespace($namespace);
EndpointProvider::setBasePath('../' . $path);
$endpoints = EndpointProvider::getAll();
sort($endpoints);

function getPartial(string $string, array $params = []): string
{
    extract($params);
    return include __DIR__ . '/../app/view/partials/' . $string . '.partial.php';
}

function has($index, array $details)
{
    return array_key_exists($index, $details);
}

function formatCode(array $code): string
{
    $string = '';
    foreach ($code as $heading => $value) {
        $string .= '<fieldset><legend>' . $heading . '</legend>';
        if (is_array($value)) {
            $string .= formatInternal($value);
        } else {
            $string .= $value;
        }
        $string .= '</fieldse><br />';
    }

    return $string;
}

function formatInternal(array $code): string
{
    $string = '';
    foreach ($code as $heading => $value) {

        if (is_array($value)) {
            $string .= $heading . ' => ';
            $string .= formatInternal($value);
        } else {
            $string .= $heading . ' => ';
            $string .= $value;
            $string .= '<br />';
        }
    }

    return $string;
}

require __DIR__ . '/index.view.php';
exit;
