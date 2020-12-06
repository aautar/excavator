<?php

$pharFile = 'excavator.phar';

if (file_exists($pharFile))  {
    unlink($pharFile);
}

$phar = new Phar($pharFile);
$phar->startBuffering();
$defaultStub = $phar->createDefaultStub('excavator.php');

$phar->addFile(__DIR__ . '/excavator.php', 'excavator.php');
$phar->addEmptyDir("src");
$files = glob(__DIR__ . "/src/*.*");
foreach($files as $f) {
    $phar->addFile($f, "src/" . basename($f));
}

$stub = "#!/usr/bin/php \n" . $defaultStub;
$phar->setStub($stub);

$phar->stopBuffering();

$phar->compressFiles(Phar::GZ);

chmod(__DIR__ . '/excavator.phar', 0770);

echo "$pharFile successfully created" . PHP_EOL;
