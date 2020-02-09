<?php

namespace Genesis\ApiSpecTests\Features\ApiSpec\Endpoint;

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

    public static function getSchema(): array
    {
        return [
            200 => [
                'headers' => [],
                'body' => [
                    'name' => [
                        'type' => self::TYPE_STRING,
                        'optional' => false,
                        'pattern' => null,
                    ],
                    'address' => [
                        'type' => self::TYPE_ARRAY,
                        'optional' => false,
                        'schema' => [
                            '0' => [
                                'type' => self::TYPE_STRING,
                                'optional' => false,
                                'pattern' => null,
                            ],
                            'jug' => [
                                'type' => self::TYPE_INTEGER,
                                'optional' => false,
                                'min' => null,
                                'max' => null,
                            ],
                            '1' => [
                                'type' => self::TYPE_STRING,
                                'optional' => false,
                                'pattern' => null,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
