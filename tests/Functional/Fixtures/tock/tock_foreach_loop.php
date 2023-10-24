<?php

use Asmblah\PhpCodeShift\Tests\Functional\Harness\Shift\Tock\TockHandler;

$myArray = ['first' => 'one', 'second' => 'two', 'third' => 'three'];

foreach ($myArray as $myKey => $myValue) {
    TockHandler::log('Iteration $myKey=' . $myKey . ', $myValue=' . $myValue);
}
