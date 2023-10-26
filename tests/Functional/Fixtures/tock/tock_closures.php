<?php

declare(strict_types=1);

namespace Asmblah\PhpCodeShift\Tests\Functional\Fixtures;

use Asmblah\PhpCodeShift\Tests\Functional\Harness\Shift\Tock\TockHandler;

$mySecondClosure = static function (int $number): int {
    TockHandler::log('Hello from mySecondClosure');

    return $number * 2;
};

$myFirstClosure = static function (int $number) use ($mySecondClosure): int {
    TockHandler::log('Hello from myFirstClosure');

    return $mySecondClosure($number) * 3;
};

$myFirstClosure(21);
