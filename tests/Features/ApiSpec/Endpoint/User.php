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
                'success' => [
                    'type' => self::TYPE_BOOLEAN,
                    'optional' => false,
                ],
                'data' => [
                    'type' => self::TYPE_OBJECT,
                    'optional' => false,
                    'schema' => [
                        'count' => [
                            'type' => self::TYPE_INTEGER,
                            'optional' => false,
                            'min' => false,
                            'max' => false,
                        ],
                        'users' => [
                            'type' => self::TYPE_OBJECT,
                            'optional' => false,
                            'schema' => [
                                'name' => [
                                    'type' => self::TYPE_STRING,
                                    'optional' => false,
                                    'pattern' => false,
                                ],
                                'dob' => [
                                    'type' => self::TYPE_STRING,
                                    'optional' => false,
                                    'pattern' => false,
                                ],
                                'smoker' => [
                                    'type' => self::TYPE_BOOLEAN,
                                    'optional' => false,
                                ],
                                'hobbies' => [
                                    'type' => self::TYPE_ARRAY,
                                    'optional' => false,
                                    'schema' => [
                                        '0' => [
                                            'type' => self::TYPE_STRING,
                                            'optional' => false,
                                            'pattern' => false,
                                        ],
                                        '1' => [
                                            'type' => self::TYPE_STRING,
                                            'optional' => false,
                                            'pattern' => false,
                                        ],
                                        '2' => [
                                            'type' => self::TYPE_STRING,
                                            'optional' => false,
                                            'pattern' => false,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
