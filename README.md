API Spec
=========

Got API's but no time to put automated tests around it? Already have automated tests but are hard to maintain? Here is an effective automated solution that will prevent regression and maintain itself on demand.

Installation
------------

```
composer require genesis/behat-api-spec
```

behat.yml file

```yaml
default:
  suites:
    default:
      contexts:
        - Genesis\BehatApiSpec\Context\ApiSpecContext
  extensions:
    Genesis\BehatApiSpec\Extension:
      baseUrl: <Your API Url>
      specMappings:
        endpoint: <Namespace to folder to autoload - leading backslash>
```

Basic overview
--------------

Create an endpoint file
```php
<?php

namespace ...;

use Genesis\BehatApiSpec\Contracts\Endpoint;

class User implements Endpoint
{
    public static function getEndpoint(): string
    {
        return '/users';
    }

    public static function getRequestHeaders(): array
    {
        return [
            'accept-language' => 'en',
            'accept' => 'text/html',
        ];
    }
}

```

Add step definition to feature file

```gherkin
Scenario: 200 user response
    When I make a POST request to the "User" endpoint
    Then I expect a 500 "User" response
    And the response should match the snapshot
```

The `When I make a POST request to "User" endpoint` will initially auto scaffold schema using the response and insert it into the endpoint file you've declared above. On subsequent calls this schema will be used to validate the response, providing protection against regression. A sample schema can be as follows for the response of a GET request with 200 response `{"success": false, "error": "Something went wrong."}`:

```php
<?php

namespace ...;

use Genesis\BehatApiSpec\Contracts\Endpoint;

class User implements Endpoint
{
    ...

    public function getResponseSchema(): array
    {
        'GET' => [
            500 => [
                'headers' => [
                    'Host' => [
                        'value' => 'localhost:8090',
                        'type' => self::TYPE_STRING,
                    ],
                    'Connection' => [
                        'value' => 'close',
                        'type' => self::TYPE_STRING,
                    ],
                    'X-Powered-By' => [
                        'value' => 'PHP/7.2.26-1+ubuntu18.04.1+deb.sury.org+1',
                        'type' => self::TYPE_STRING,
                    ],
                    'content-type' => [
                        'value' => 'application/json',
                        'type' => self::TYPE_STRING,
                    ],
                ],
                'body' => [
                    'success' => [
                        'type' => self::TYPE_BOOLEAN,
                        'optional' => false,
                    ],
                    'error' => [
                        'type' => self::TYPE_STRING,
                        'optional' => false,
                        'pattern' => null,
                    ],
                ],
            ]
        ]
    }
}

```

Adjust accordingly.

Snapshots
---------

Following on from this the `And the response should match the snapshot` will generate a snapshot automatically storing the response against the scenario title. This will be stored in the same directory as the test. This file should be committed with the code to allow it to be peer reviewed. Upon subsequent requests, the response will be matched with this snapshot, any difference will generate a failure. You have either the option to update the snapshot automatically using the `--update-snapshots`, `-u` flag or fix the issue in the API. Any out of date snapshots will be identified and updated with the flag appropriately. Example snapshot:

```php
<?php return [

    '500 user response' =>
        '{"success":false,"error":"Something went wrong."}',

];

```

Placeholders
------

All requests go call on the PlaceHolderService::resolveInString method with the body and url to replace any placeholders (format - `{{placeholder_name}}`) you may have set using the default preRequestCallable hook which is overridable (See hooks section). To add placeholders, you can use the PlaceholderService like so:

```php

    public function ...
    {
        $value = ...;
        PlaceholderService::add($name, $value);

        PlaceHolderService::getValue($name); // returns $value;
    }

```

Placeholders are reset after every scenario to prevent test session bleed. Example usage in feature file:

```gherkin
    Scenario: 200 user response
        When I make a POST request to the "User" endpoint with body:
            """
                {"status": "{{status_failed}}"}
            """
```

In the above example if you've set `PlaceHolderService::add('status_failure', -1)` then expect `{"status": "-1"}` to be sent as the body. Note values have to scalar to be part of the body.

Hooks
------

Pre request and post request hooks can be configured per context configuration in the behat.yml file like so:

```
behat.yml file

```yaml
default:
  suites:
    default:
      contexts:
        - Genesis\BehatApiSpec\Context\ApiSpecContext:
            preRequestCallable: 'MyClass::preRequestStaticCallable'
            postRequestCallable: 'MyClass::postRequestStaticCallable'
```

Generating sample requests
--------------------------

If you use the step definition `When I make a POST request to "User" endpoint` to send requests to the API, you can use the `--sample-request=<format>` flag to generate sample requests to execute quickly through the command line. An example would be:

`vendor/bin/behat --sample-request=curl`

```
Scenario: 200 user response
    Given I set the following headers:
      | content-type | application/json |
      | accept       | en               |
    When I make a GET request to "User" endpoint
      │ curl -X GET --header 'content-type: application/json' --header 'accept: text/html' --header 'accept-language: en' 'http://localhost:8090/index.php/users'
```

Note the curl command generated below the step definition.

Step defintions
----------------

More step definitions are provided as part of the context file for validation of the API response. Find out using `vendor/bin/behat -dl`.
