<?php

require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Excavator\Output;
use Excavator\S3ArtifactDownloader;
use Excavator\DataMigrator;

$stdout = new Output();

$stdout->writeLine("Excavator 1.1.0");

$s3Bucket = getenv('S3_BUCKET');
$s3AccessKey = getenv('S3_ACCESS_KEY');
$s3SecretKey = getenv('S3_SECRET_KEY');
$s3Region = getenv('S3_REGION');
$dbConnections = getenv('DB_CONNECTIONS');

if(!isset($argv[1]) || !isset($argv[2])) {
    $stdout->writeLine("Missing argument(s)\n");
    $stdout->writeLine("excavator [ARTIFACT-ZIP] [DESTINATION-FOLDER]\n");
    exit;
}

if(empty($s3Bucket) || empty($s3AccessKey) || empty($s3SecretKey) || empty($s3Region)) {
    $stdout->writeLine("Missing require environment variables");
    exit;
}

$databaseConnectionStrings = json_decode(empty($dbConnections) ? '[]' : $dbConnections);
if($databaseConnectionStrings === null || !is_array($databaseConnectionStrings)) {
    $stdout->writeLine("Failed to parse database connections: " . $dbConnections);
    exit;
}

$stdout->writeLine('S3_BUCKET=' . $s3Bucket);
$stdout->writeLine('S3_ACCESS_KEY=' . $s3AccessKey);
$stdout->writeLine('S3_REGION=' . $s3Region);

$dataMigrators = [];
if(empty($databaseConnectionStrings)) {
    $stdout->writeLine("No database connection specified, will not attempt to run migrations");
} else {
    foreach($databaseConnectionStrings as $cs) {
        try {
            $dm = new DataMigrator($cs);
            $dm->checkDatabaseConnections();
            $dataMigrators[] = $dm;
        } catch (\Throwable $e) {
            $stdout->writeLine($e->getMessage());
            exit;
        }
    }
}

$s3 = new S3Client([
    'version' => 'latest',
    'region'  => $s3Region,
    'credentials' => [
        'key' => $s3AccessKey,
        'secret' => $s3SecretKey,
    ]
]);

$stdout->writeMessageStart("Downloading artifact... ");
$s3ArtifactDownloader = new S3ArtifactDownloader($s3);
$saveToFilename = $s3ArtifactDownloader->downloadToTempFile($s3Bucket, $argv[1]);
$stdout->writeMessageEnd("done.");

$stdout->writeMessageStart("Unzipping artifact...");
$zip = new ZipArchive();
$res = $zip->open($saveToFilename);
if ($res === TRUE) {
    $zip->extractTo($argv[2]);
    $zip->close();
} else {
    $stdout->writeMessageEnd("Failed to open " . $saveToFilename . ".");
}

$stdout->writeMessageEnd("done.");
