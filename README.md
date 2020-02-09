API Spec
=========

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

    public static function getHeaders(): array
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
When I make a POST request to "User" endpoint with body:
    """
    ...
    """
Then I expect a 200 "User" response
```

This will append the current schema of the response in the endpoint file you've just created, saving you lots of time in typing it all out. Running it again will ensure that the rules are then validated against, ensuring that the API doesn't regress. You can add further assertion of the exact response you want or constraints on the schema in general.
