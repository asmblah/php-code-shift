<?php

use My\Stuff\MyClass;

// NB: The references to MyClass will be hooked and repointed to MyReplacementClass1
//     by the shift defined inside ClassHookShiftTypeTest.
$myObject = new MyClass();

return [
    'getViaInstanceMethod()' => $myObject->getViaInstanceMethod(),
    '::MY_CONST' => MyClass::MY_CONST,
    'getViaStaticMethod()' => MyClass::getViaStaticMethod(),
];
