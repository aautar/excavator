<?php

require 'vendor/autoload.php';

use Aws\S3\S3Client;

echo "Excavator 1.0.0\n";

$s3Bucket = getenv('S3_BUCKET');
$s3AccessKey = getenv('S3_ACCESS_KEY');
$s3SecretKey = getenv('S3_SECRET_KEY');
$s3Region = getenv('S3_REGION');

if(!isset($argv[1]) || !isset($argv[2])) {
    echo "Missing argument(s)\n\n";
    echo "excavator [ARTIFACT-ZIP] [DESTINATION-FOLDER]\n\n";
    exit;
}

if(empty($s3Bucket) || empty($s3AccessKey) || empty($s3SecretKey) || empty($s3Region)) {
    echo "Missing require environment variables\n";
    exit;
}

echo 'S3_BUCKET=' . $s3Bucket . "\n";
echo 'S3_ACCESS_KEY=' . $s3AccessKey . "\n";
echo 'S3_REGION=' . $s3Region . "\n";

$s3 = new S3Client([
    'version' => 'latest',
    'region'  => $s3Region,
    'credentials' => [
        'key' => $s3AccessKey,
        'secret' => $s3SecretKey,
    ]
]);

echo "Downloading artifact... ";

$saveToFilename = tempnam(sys_get_temp_dir(), 'excavator-artifact-');
$result = $s3->getObject([
    'Bucket' => $s3Bucket,
    'Key' => $argv[1],
    'SaveAs' => $saveToFilename
]);

echo "done.\n";

echo "Unzipping artifact...";
$zip = new ZipArchive();
$res = $zip->open($saveToFilename);
if ($res === TRUE) {
    $zip->extractTo($argv[2]);
    $zip->close();
} else {
    echo "Failed to open " . $saveToFilename . ".";
}

echo "done.\n";
