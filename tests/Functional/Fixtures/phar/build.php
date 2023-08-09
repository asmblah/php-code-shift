<?php

// Usage: "php -d phar.readonly=0 tests/Functional/Fixtures/phar/build.php"

declare(strict_types=1);

$phar = new Phar(__DIR__ . '/substr_in_phar.phar');

$phar->startBuffering();
$defaultStub = $phar->createDefaultStub('index.php');
$phar->buildFromDirectory(__DIR__ . '/src');

// Add hashbang to stub.
$stub = '#!/usr/bin/env php' . PHP_EOL . $defaultStub;

$phar->setStub($stub);

$phar->stopBuffering();

$phar->compressFiles(Phar::GZ);

//chmod(__DIR__ . "/{$pharFile}", 0770);
