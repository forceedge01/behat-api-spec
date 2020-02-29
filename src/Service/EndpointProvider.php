<?php

namespace Genesis\BehatApiSpec\Service;

use Exception;
use Genesis\BehatApiSpec\Entity\Endpoint;
use ReflectionClass;

class EndpointProvider
{
    private static $baseNamespace;

    private static $basePath;

    public static function setBaseNamespace(string $namespace): void
    {
        self::$baseNamespace = $namespace;
    }

    public static function setBasePath(string $path): void
    {
        self::$basePath = $path;
    }

    public function getApiSpecEndpointClass(string $class): string
    {
        return self::$baseNamespace . $class;
    }

    public static function getFilename(string $endpoint): string
    {
        return (new ReflectionClass($endpoint))->getFileName();
    }

    public static function getAbsolutePath(): string
    {
        return realpath(self::$basePath);
    }

    public function getAll(): array
    {
        $path = self::getAbsolutePath();

        if (empty($path)) {
            throw new Exception('The path could not be converted to an absolute path.');
        }

        return self::getRecursiveEndpoints($path);
    }

    private function getRecursiveEndpoints(string $path): array
    {
        $files = scandir($path);
        $endpoints = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $absolute = $path . DIRECTORY_SEPARATOR . $file;
            if (is_dir($absolute)) {
                $endpoints += self::getRecursiveEndpoints($absolute);
            }

            $endpoints[] = new Endpoint(self::$baseNamespace . pathinfo($file, PATHINFO_FILENAME));
        }

        return $endpoints;
    }
}
