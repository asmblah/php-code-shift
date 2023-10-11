<?php

declare(strict_types=1);

namespace Asmblah\PhpCodeShift\Tests\Functional\Fixtures;

function myFirstFunction(int $number): int
{
    return mySecondFunction($number) * 3;
}

function mySecondFunction(int $number): int
{
    return $number * 2;
}
