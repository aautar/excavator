<?php

require __DIR__ . '/vendor/autoload.php';

use Aws\S3\S3Client;
use Excavator\Output;
use Excavator\S3ArtifactDownloader;
use Excavator\DataMigrator;
use Excavator\ResourcePath;
use Excavator\S3ResourcePath;

$stdout = new Output();

$stdout->writeLine("Excavator 1.1.0");

/**
 * e.g. s3://access:secret@region.bucket
 */
$s3Path = getenv('S3_PATH');

/**
 * e.g. deploy/artifact-release-%tag%.zip
 */
$artifactPathTemplate = getenv('ARTIFACT_PATH_TEMPLATE');

/**
 * e.g. sql/%dbname%-migration-%tag%.sql
 */
$dbMigrationPathTemplate = getenv('DB_MIGRATION_PATH_TEMPLATE');

/**
 * e.g. mysql://root:rootpass@localhost:3306/mydb
 */
$dbConnectionPath = getenv('DB_CONNECTION');

if(!isset($argv[1]) || !isset($argv[2])) {
    $stdout->writeLine("Missing argument(s)\n");
    $stdout->writeLine("excavator [VERSION-TAG] [DESTINATION-FOLDER]\n");
    exit;
}

if(empty($s3Path) || empty($artifactPathTemplate)) {
    $stdout->writeLine("Missing require environment variables");
    exit;
}

if(!empty($dbConnectionPath)) {
    if(empty($dbMigrationPathTemplate)) {
        $stdout->writeLine("Missing require environment variables: DB_MIGRATION_PATH_TEMPLATE");
        exit;
    }
}

$versionTag = $argv[1];
$artifactZip = str_replace("%tag%", $versionTag, $artifactPathTemplate);
$destinationFolder = $argv[2];

$s3ResourcePath = new S3ResourcePath($s3Path);

$s3AccessKey = $s3ResourcePath->getUser();
$s3SecretKey = $s3ResourcePath->getPass();
$s3Bucket = $s3ResourcePath->getBucket();
$s3Region = $s3ResourcePath->getRegion();

$s3 = new S3Client([
    'version' => 'latest',
    'region'  => $s3Region,
    'credentials' => [
        'key' => $s3AccessKey,
        'secret' => $s3SecretKey,
    ]
]);

// Download artifact
$stdout->writeMessageStart("Downloading artifact... ");
$s3ArtifactDownloader = new S3ArtifactDownloader($s3);
$artifact = $s3ArtifactDownloader->download($s3Bucket, $artifactZip, $versionTag);
$stdout->writeMessageEnd("done.");


// Run DB migrations
if(empty($dbConnectionPath)) {
    $stdout->writeLine("No database connection specified, will not attempt to run migrations");
} else {
    $dbResourcePath = new ResourcePath($dbConnectionPath);
    $dbScriptPath = str_replace("%tag%", $versionTag, $dbMigrationPathTemplate);
    $dbScriptPath = str_replace("%dbname%", $dbResourcePath->getPath(), $dbScriptPath);
    $dbScriptPath = trim($dbScriptPath, "/");

     try {

        $migrator = new DataMigrator($dbResourcePath, $artifact, $dbScriptPath);

        $stdout->writeMessageStart("Checking database connections for migrations... ");
        $migrator->checkDatabaseConnection();
        $stdout->writeMessageEnd("done.");

        $stdout->writeMessageStart("Executing DB migrations ({$dbScriptPath})... ");
        $executedScript = $migrator->executeMigration();

        if($executedScript) {
            $stdout->writeMessageEnd("done (executed script).");
        } else {
            $stdout->writeMessageEnd("done (no script found).");
        }

     } catch (\Throwable $e) {
         $stdout->writeLine($e->getMessage());
         exit;
     }
}

// Unzip artifact
$stdout->writeMessageStart("Unzipping artifact... ");
$artifact->unzipAll($destinationFolder);
$stdout->writeMessageEnd("done.");

$artifact->cleanup();