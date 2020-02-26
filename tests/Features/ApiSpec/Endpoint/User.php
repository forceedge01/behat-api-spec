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
            'GET' => [
                200 => self::get200GETResponseSchema(),
                500 => self::get500GETResponseSchema(),
                201 => self::get201GETResponseSchema(),
            ],
            'POST' => [
                200 => self::get200POSTResponseSchema(),
            ],
        ];
    }

    public static function getQueryParams(): array
    {
        return [
            'test' => [
                'type' => self::TYPE_STRING,
                'description' => '',
                'example' => 'true',
                'pattern' => '',
            ],
            'exception' => [
                'type' => self::TYPE_STRING,
                'description' => '',
                'example' => '1',
                'pattern' => '',
            ],
            'error' => [
                'type' => self::TYPE_STRING,
                'description' => '',
                'example' => 'message is bad',
                'pattern' => '',
            ],
            'errorCode' => [
                'type' => self::TYPE_STRING,
                'description' => '',
                'example' => '503',
                'pattern' => '',
            ],
        ];
    }

    public static function get200GETResponseSchema(): array
    {
        return [
            'headers' => [
                'Host' => [
                    'value' => 'localhost:8090',
                    'type' => self::TYPE_STRING,
                ],
                'Date' => [
                    'value' => 'Wed, 26 Feb 2020 10:00:24 GMT',
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
                            'type' => self::TYPE_STRING,
                            'optional' => false,
                            'pattern' => null,
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

    public static function get500GETResponseSchema(): array
    {
        return [
            'headers' => [
                'Host' => [
                    'value' => 'localhost:8090',
                    'type' => self::TYPE_STRING,
                ],
                'Date' => [
                    'value' => 'Wed, 26 Feb 2020 10:00:24 GMT',
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
        ];
    }

    public static function get201GETResponseSchema(): array
    {
        return [
            'headers' => [
                'Host' => [
                    'value' => 'localhost:8090',
                    'type' => self::TYPE_STRING,
                ],
                'Date' => [
                    'value' => 'Wed, 26 Feb 2020 10:00:24 GMT',
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
                'data' => [
                    'type' => self::TYPE_ARRAY,
                    'optional' => false,
                    'schema' => [
                        '0' => [
                            'type' => self::TYPE_OBJECT,
                            'optional' => false,
                            'schema' => [
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
                        ],
                        '1' => [
                            'type' => self::TYPE_OBJECT,
                            'optional' => false,
                            'schema' => [
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
                                            'type' => self::TYPE_STRING,
                                            'optional' => false,
                                            'pattern' => null,
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
                        '2' => [
                            'type' => self::TYPE_OBJECT,
                            'optional' => false,
                            'schema' => [
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
                                            'type' => self::TYPE_STRING,
                                            'optional' => false,
                                            'pattern' => null,
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
                    ],
                ],
            ],
        ];
    }

    public static function get200POSTResponseSchema(): array
    {
        return [
            'headers' => [
                'Host' => [
                    'value' => 'localhost:8090',
                    'type' => self::TYPE_STRING,
                ],
                'Date' => [
                    'value' => 'Wed, 26 Feb 2020 10:00:24 GMT',
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
                'id' => [
                    'type' => self::TYPE_INTEGER,
                    'optional' => false,
                    'min' => null,
                    'max' => null,
                ],
            ],
        ];
    }
}
