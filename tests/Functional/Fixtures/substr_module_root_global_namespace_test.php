<?php

// NB: substr(...) will be hooked by the shift defined inside HookBuiltinFunctionTest.
$myResult = substr('my string', 1, 4) . ' and ' . substr('your string', 1, 2);

return $myResult;
