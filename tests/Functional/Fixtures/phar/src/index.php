<?php

print '[before] ';

$path = __DIR__ . '/substr_in_phar_src.php';

print require $path;

print ' [after]';
