<?php

use Asmblah\PhpCodeShift\Tests\Functional\Harness\Shift\Tock\TockHandler;

for ($i = 0; $i < 4; $i++) {
    TockHandler::log('Iteration #' . $i);
}
