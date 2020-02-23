<?php

namespace Genesis\BehatApiSpec\Service;

use Exception;

class Snapshot
{
    public static function getSnapshotTitle($currentScenario): string
    {
        $title = $currentScenario->getScenario()->getTitle();
        if (!$title) {
            throw new Exception('In order to create a snapshot, please declare scenario title.');
        }

        return strtolower(str_replace([' '], '-', basename($title)));
    }

    public static function getSnapshotPath($currentScenario): string
    {
        $featurePath = $currentScenario->getFeature()->getFile();

        return substr($featurePath, 0, strrpos($featurePath, '/'))
            . DIRECTORY_SEPARATOR
            . '__snapshots__'
            . DIRECTORY_SEPARATOR
            . strtolower(str_replace([' '], '-', basename($featurePath)))
            . '.txt';
    }

    public static function createSnapshotDir(string $dir)
    {
        if (!is_dir(dirname($dir))) {
            mkdir(dirname($dir), 0777, true);
        }
    }

    public static function fileExists(string $file): bool
    {
        return file_exists($file);
    }

    public static function exists(string $file, string $scenario): bool
    {
        if (self::getSnapshot($file, $scenario)) {
            return true;
        }

        return false;
    }

    public static function getSnapshots(string $file): ?array
    {
        if (!self::fileExists($file)) {
            return null;
        }

        return unserialize(file_get_contents($file));
    }

    public static function getSnapshot(string $file, string $scenario): ?string
    {
        $contents = self::getSnapshots($file);

        if ($contents === null) {
            return null;
        }

        return $contents[$scenario] ?? null;
    }

    public static function remove(string $file, string $scenario)
    {
        $contents = self::getSnapshots($file);
        unset($contents[$scenario]);

        return file_put_contents($file, serialize($contents)) > 0;
    }

    public static function save(string $file, string $scenario, string $snapshot): bool
    {
        $contents = self::getSnapshots($file);
        $contents[$scenario] = $snapshot;

        return file_put_contents($file, serialize($contents)) > 0;
    }
}
