<?php

use Asmblah\PhpCodeShift\Tests\Functional\Harness\Shift\Tock\TockHandler;

$i = 0;

do {
    TockHandler::log('Iteration $i=' . $i);
} while ($i++ < 4);
