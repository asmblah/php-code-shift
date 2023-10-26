<?php

// NB: substr(...) will be hooked by the shift defined inside HookBuiltinFunctionTest.
$myResult = substr(
    'my string',
    // A comment here.
    1,


    // Some whitespace and comment there.
    4) .
    ' and ' . substr(
        'your string', 1, 2 // Some odd formatting here.
    );

return $myResult;
