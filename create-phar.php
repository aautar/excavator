<?php

function getFilesInDirectory(string $path): array {
    $files = [];
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

    foreach ($rii as $file) {
        if ($file->isDir()){ 
            continue;
        }
    
        $files[] = $file->getPathname();    
    }

    return $files;
}

function removeFilesInDirectory(string $path): array {
    $files = [];
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS));

    foreach ($rii as $file) {
        if ($file->isDir()){
            continue;
        }
    
        $fp = $file->getPathname();
        $files[] = $fp;
        unlink($fp);
    }

    return $files;
}

function removeEmptyDirectoriesInDirectory(string $path) {
    $files = [];
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);

    foreach ($rii as $f) {
        if ($f->isDir()){
            rmdir($f);
        }
    }
}

function createPhar() {
    $pharFile = 'excavator.phar';

    if (file_exists($pharFile))  {
        unlink($pharFile);
    }

    $phar = new Phar($pharFile);
    $phar->startBuffering();
    $defaultStub = $phar->createDefaultStub('excavator.php');

    mkdir(__DIR__ . "/phar-staging");

    copy(__DIR__ . '/excavator.php', __DIR__ . "/phar-staging/excavator.php");

    // Add src files
    $files = getFilesInDirectory(__DIR__ . "/src");
    foreach($files as $f) {
        $pathInPhar = substr($f, strlen(__DIR__) + 1);
        echo "copying to phar-staging: {$pathInPhar}...\n";
        @mkdir(dirname(__DIR__ . "/phar-staging/{$pathInPhar}"), 0777, true);
        copy($f, __DIR__ . "/phar-staging/{$pathInPhar}");
    }

    // Add vendor files
    $phar->addEmptyDir("vendor");
    $files = getFilesInDirectory(__DIR__ . "/vendor");
    foreach($files as $f) {
        $pathInPhar = substr($f, strlen(__DIR__) + 1);
        echo "adding to phar-staging: {$pathInPhar}...\n";
        @mkdir(dirname(__DIR__ . "/phar-staging/{$pathInPhar}"), 0777, true);
        copy($f, __DIR__ . "/phar-staging/{$pathInPhar}");
    }

    echo "creating excavator.phar...\n";
    $phar->buildFromDirectory(__DIR__ . "/phar-staging");

    $stub = "#!/usr/bin/php \n" . $defaultStub;
    $phar->setStub($stub);

    $phar->stopBuffering();

    $phar->compressFiles(Phar::GZ);

    chmod(__DIR__ . '/excavator.phar', 0770);

    echo "cleanup up...\n";
    removeFilesInDirectory(__DIR__ . "/phar-staging");
    removeEmptyDirectoriesInDirectory(__DIR__ . "/phar-staging");
    rmdir(__DIR__ . "/phar-staging");

    echo "$pharFile successfully created" . PHP_EOL;
}

createPhar();
