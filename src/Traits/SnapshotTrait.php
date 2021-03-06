<?php

namespace Genesis\BehatApiSpec\Traits;

use Exception;
use Genesis\BehatApiSpec\Service\RequestHandler;
use Genesis\BehatApiSpec\Service\Snapshot;
use Genesis\BehatApiSpec\Validators\StringValidator;

trait SnapshotTrait
{
    private static $validSnapshots = [];

    private static $updateSnapshots;

    private static $updatedSnapshots = 0;

    private static $isSnapshotScenario = false;

    /**
     * @AfterScenario
     */
    public function storeObsoleteFiles()
    {
        if (!self::$isSnapshotScenario) {
            return;
        }

        self::$validSnapshots[Snapshot::getSnapshotPath(self::$currentScenario)][] = Snapshot::getSnapshotTitle(self::$currentScenario);
    }

    /**
     * @Then the response should match the snapshot pattern
     */
    public function theResponseShouldMatchTheSnapshot()
    {
        $title = Snapshot::getSnapshotTitle(self::$currentScenario);
        $path = Snapshot::getSnapshotPath(self::$currentScenario);
        $actualResponse = RequestHandler::getResponseBody();
        Snapshot::createSnapshotDir($path);

        if (Snapshot::exists($path, $title)) {
            $expected = Snapshot::getSnapshot($path, $title);
            try {
                StringValidator::validate($actualResponse, ['pattern' => $expected]);
            } catch (Exception $e) {
                if (! self::$updateSnapshots) {
                    echo 'Update snapshot with --update-snapshots or -u flag.';
                    throw $e;
                }

                echo 'Updating snapshot... ';
                Snapshot::save($path, $title, '/^' . preg_quote($actualResponse, '/') . '$/');
                self::$updatedSnapshots++;
            }
        } else {
            echo 'Generating snapshot: ' . $title;
            Snapshot::save($path, $title, $actualResponse);
        }

        self::$isSnapshotScenario = true;
    }


    /**
     * @Then the response should match the snapshot
     */
    public function theResponseShouldEqualTheSnapshot()
    {
        $title = Snapshot::getSnapshotTitle(self::$currentScenario);
        $path = Snapshot::getSnapshotPath(self::$currentScenario);
        $actualResponse = RequestHandler::getResponseBody();
        Snapshot::createSnapshotDir($path);

        if (Snapshot::exists($path, $title)) {
            $expected = Snapshot::getSnapshot($path, $title);
            try {
                StringValidator::validate($actualResponse, ['value' => $expected]);
            } catch (Exception $e) {
                if (! self::$updateSnapshots) {
                    echo 'Update snapshot with --update-snapshots or -u flag.';
                    throw $e;
                }

                echo 'Updating snapshot... ';
                Snapshot::save($path, $title, $actualResponse);
                self::$updatedSnapshots++;
            }
        } else {
            echo 'Generating snapshot: ' . $title;
            Snapshot::save($path, $title, $actualResponse);
        }

        self::$isSnapshotScenario = true;
    }

    /**
     * @AfterSuite
     */
    public static function displayUpdatedSnapshots()
    {
        if (self::$updateSnapshots) {
            echo 'Updated snapshot(s): ' . self::$updatedSnapshots;
        }
    }

    /**
     * @AfterSuite
     */
    public static function displayObsoleteFiles()
    {
        // Go through each directory and check for files that don't exist.
        $obsoleteFiles = [];
        $obsoleteSnapshots = [];
        foreach (self::$validSnapshots as $snapshotFile => $snapshots) {
            $directory = dirname($snapshotFile);
            $scannedFiles = scandir($directory);
            foreach ($scannedFiles as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                if (!in_array($snapshotFile, array_keys(self::$validSnapshots))) {
                    $obsoleteFiles[] = snapshotFile;
                    continue;
                }
            }

            $storedSnapshots = Snapshot::getSnapshots($snapshotFile);
            foreach ($storedSnapshots as $scenario => $storedSnapshot) {
                if (!in_array($scenario, $snapshots)) {
                    $featureFile = Snapshot::getFeaturePath($snapshotFile);
                    if ($featureFile) {
                        if (!preg_match('/^\s*Scenario\s*:\s*' . preg_quote($scenario).'$/im', file_get_contents($featureFile))) {
                            echo '^\s*Scenario\s*:\s*' . preg_quote($scenario).'$' . PHP_EOL;
                            $obsoleteSnapshots[$snapshotFile][] = $scenario;
                        }
                    }
                }
            }
        }

        if ($obsoleteFiles) {
            echo 'Obsolete files:' . PHP_EOL;
            if (self::$updateSnapshots) {
                echo 'Deleting obsolete files...' . PHP_EOL . PHP_EOL;
            }
            foreach ($obsoleteFiles as $file) {
                echo $file . PHP_EOL;
                if (self::$updateSnapshots) {
                    unlink($file);
                }
            }
        }

        if ($obsoleteSnapshots) {
            echo 'Obsolete snapshots:' . PHP_EOL;
            if (self::$updateSnapshots) {
                echo 'Removing snapshots...' . PHP_EOL . PHP_EOL;
            }
            foreach ($obsoleteSnapshots as $file => $snapshots) {
                foreach ($snapshots as $snapshot) {
                    echo $snapshot . PHP_EOL;
                    if (self::$updateSnapshots) {
                        Snapshot::remove($file, $snapshot);
                    }
                }
            }
        }
    }

    public static function setUpdateSnapshots(bool $bool)
    {
        self::$updateSnapshots = $bool;
    }
}
