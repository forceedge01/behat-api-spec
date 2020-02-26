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
    const TYPE_DATE = 'date';
    const TYPE_DATETIME = 'datetime';
    const TYPE_ENUM = 'enum';
    const TYPE_CALLBACK = 'callback';

    const _HEADERS = 'headers';
    const _BODY = 'body';
    const _QUERY_PARAMS = 'queryParams';
    const _SCHEMA = 'schema';
    const _TEMPLATE = '*';
    const _TYPE = 'type';
    const _OPTIONAL = 'optional';
    const _PATTERN = 'pattern';

    public static function getEndpoint(): string;

    public static function getHeaders(): array;
}
