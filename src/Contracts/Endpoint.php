<?php

namespace Genesis\BehatApiSpec\Contracts;

interface Endpoint
{
    const TYPE_STRING = 'string';
    const TYPE_PHONE = 'phone';
    const TYPE_EMAIL = 'email';
    const TYPE_NUMBER = 'number';
    const TYPE_INTEGER = 'integer';
    const TYPE_BOOLEAN = 'type:boolean';
    const TYPE_ANY = 'any';
    const TYPE_OBJECT = 'object';
    const TYPE_ARRAY = 'array';

    public static function getEndpoint(): string;

    public static function getHeaders(): array;
}
