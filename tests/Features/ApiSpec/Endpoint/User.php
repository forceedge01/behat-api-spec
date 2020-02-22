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
            200 => self::get200SchemaResponse(),
            500 => self::get500SchemaResponse(),
            201 => self::get201SchemaResponse(),
        ];
    }

    public static function get200SchemaResponse(): array
    {
        return [
            'headers' => [
                'Host' => [
                    'value' => 'localhost:8090',
                    'type' => self::TYPE_STRING,
                ],
                'Date' => [
                    'pattern' => '/.*/',
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
        ];
    }

    public static function get500SchemaResponse(): array
    {
        return [
            'headers' => [
                'Host' => [
                    'value' => 'localhost:8090',
                    'type' => self::TYPE_STRING,
                ],
                'Date' => [
                    'pattern' => '/.*/',
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
        ];
    }

    public static function get201SchemaResponse(): array
    {
        return [
            'headers' => [
                'Host' => [
                    'value' => 'localhost:8090',
                    'type' => self::TYPE_STRING,
                ],
                'Date' => [
                    'pattern' => '/.*/',
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
                'msg' => [
                    'type' => self::TYPE_STRING,
                    'optional' => false,
                    'pattern' => null,
                ],
                'id' => [
                    'type' => self::TYPE_STRING,
                    'optional' => false,
                    'pattern' => null,
                ],
            ],
        ];
    }
}
