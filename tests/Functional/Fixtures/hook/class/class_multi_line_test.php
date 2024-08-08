<?php

use My\Stuff\MyClass;

// NB: The references to MyClass will be hooked and repointed to MyReplacementClass
//     by the shift defined inside ClassHookShiftTypeTest.
$myObject = new
    MyClass();

return [
    'getViaInstanceMethod()' => $myObject
        // A comment here.
        ->

        getViaInstanceMethod(),
    '::MY_CONST' => MyClass::
        MY_CONST,
    'getViaStaticMethod()' =>       MyClass // Some odd formatting here.
        ::

        // Some whitespace and comment there.
        getViaStaticMethod(),
];
