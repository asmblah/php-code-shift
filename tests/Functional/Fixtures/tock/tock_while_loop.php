<?php

use Asmblah\PhpCodeShift\Tests\Functional\Harness\Shift\Tock\TockHandler;

$i = 0;

while ($i++ < 4) {
    TockHandler::log('Iteration $i=' . $i);
}
