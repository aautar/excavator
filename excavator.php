<?php

require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Excavator\Output;
use Excavator\S3ArtifactDownloader;
use Excavator\DataMigrationManager;

$stdout = new Output();

$stdout->writeLine("Excavator 1.1.0");

$s3Bucket = getenv('S3_BUCKET');
$s3AccessKey = getenv('S3_ACCESS_KEY');
$s3SecretKey = getenv('S3_SECRET_KEY');
$s3Region = getenv('S3_REGION');
$versionFile = getenv('VERSION_FILE');
$dbMigrationsFolder = getenv('DB_MIGRATIONS_FOLDER');
$dbConnections = getenv('DB_CONNECTIONS');

if(!isset($argv[1]) || !isset($argv[2])) {
    $stdout->writeLine("Missing argument(s)\n");
    $stdout->writeLine("excavator [ARTIFACT-ZIP] [DESTINATION-FOLDER]\n");
    exit;
}

if(empty($s3Bucket) || empty($s3AccessKey) || empty($s3SecretKey) || empty($s3Region) || empty($versionFile)) {
    $stdout->writeLine("Missing require environment variables");
    exit;
}

$databaseConnectionStrings = json_decode(empty($dbConnections) ? '[]' : $dbConnections);
if($databaseConnectionStrings === null || !is_array($databaseConnectionStrings)) {
    $stdout->writeLine("Failed to parse database connections: " . $dbConnections);
    exit;
}

$artifactZip = $argv[1];
$destinationFolder = $argv[2];

$stdout->writeLine('S3_BUCKET=' . $s3Bucket);
$stdout->writeLine('S3_ACCESS_KEY=' . $s3AccessKey);
$stdout->writeLine('S3_REGION=' . $s3Region);

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
$artifact = $s3ArtifactDownloader->download($s3Bucket, $artifactZip, $versionFile);
$stdout->writeMessageEnd("done.");

// Get version tag
$stdout->writeLine("Version tag: " . $artifact->getVersionTag());

// Run DB migrations
if(empty($databaseConnectionStrings)) {
    $stdout->writeLine("No database connection specified, will not attempt to run migrations");
} else {

     if(empty($dbMigrationsFolder)) {
         $stdout->writeLine("Missing required environment variables for DB migrations");
         exit;
     }

     $dbMigrationsFolder = $dbMigrationsFolder . "/";

     try {

        $dataMigrationManager = new DataMigrationManager($databaseConnectionStrings, $dbMigrationsFolder, $artifact);

        $stdout->writeMessageStart("Checking database connections for migrations... ");
        $dataMigrationManager->checkConnections();
        $stdout->writeMessageEnd("done.");

        $stdout->writeMessageStart("Executing DB migrations... ");
        $dataMigrationManager->executeMigrations();
        $stdout->writeMessageEnd("done.");

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