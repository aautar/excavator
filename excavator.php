<?php

require 'vendor/autoload.php';

use Aws\S3\S3Client;

echo "Excavator 1.0.0\n";

$s3Bucket = getenv('S3_BUCKET');
$s3AccessKey = getenv('S3_ACCESS_KEY');
$s3SecretKey = getenv('S3_SECRET_KEY');
$s3Region = getenv('S3_REGION');

if(!isset($argv[1])) {
    echo "Missing argument\n";   
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

$result = $s3->getObject([
    'Bucket' => $s3Bucket,
    'Key' => $argv[1],
    'SaveAs' => 'test.zip'
]);

var_dump($result);