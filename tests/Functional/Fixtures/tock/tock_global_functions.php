<?php

declare(strict_types=1);

namespace Asmblah\PhpCodeShift\Tests\Functional\Fixtures;

use Asmblah\PhpCodeShift\Tests\Functional\Harness\Shift\Tock\TockHandler;

function myFirstFunction(int $number): int
{
    TockHandler::log('Hello from myFirstFunction');

    return mySecondFunction($number) * 3;
}

function mySecondFunction(int $number): int
{
    TockHandler::log('Hello from mySecondFunction');

    return $number * 2;
}

myFirstFunction(21);
