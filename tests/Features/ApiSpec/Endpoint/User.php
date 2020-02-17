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
            201 => self::get201SchemaResponse(),
        ];
    }

    public static function get200SchemaResponse(): array
    {
        return [
            'headers' => [],
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
            'headers' => [],
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
