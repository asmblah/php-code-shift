# PHP Code Shift.

[![Build Status](https://github.com/asmblah/php-code-shift/workflows/CI/badge.svg)](https://github.com/asmblah/php-code-shift/actions?query=workflow%3ACI)

[EXPERIMENTAL] Allows running PHP code to be monkey-patched on the fly.

## Why?
To allow stubbing of built-in functions during testing, for example.

## Usage
Install this package with Composer:

```shell
$ composer install asmblah/php-code-shift
```

### Hooking built-in functions

`runner.php`

```php
<?php

use Asmblah\PhpCodeShift\CodeShift;
use Asmblah\PhpCodeShift\Shifter\Filter\FileFilter;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\FunctionHook\FunctionHookShiftSpec;

require_once __DIR__ . '/vendor/autoload.php';

$codeShift = new CodeShift();

$codeShift->shift(
    new FunctionHookShiftSpec(
        'substr',
        function (callable $originalSubstr) {
            return function (string $string, int $offset, ?int $length = null) use ($originalSubstr) {
                return '[substr<' . $originalSubstr($string, $offset, $length) . '>]';
            };
        }
    ),
    new FileFilter(__DIR__ . '/substr_test.php')
);

include __DIR__ . '/substr_test.php';
```

`substr_test.php`
```php
<?php
// NB: substr(...) will be hooked by the shift defined inside HookBuiltinFunctionTest.
$myResult = substr('my string', 1, 4) . ' and ' . substr('your string', 1, 2);

print $myResult;
```

The output will be:
```
[substr<y st>] and [substr<ou>]
```

## Limitations
Functionality is extremely limited at the moment, you may well be better off using one of the alternatives
listed in [See also](#see-also) below.

- Does not yet support `eval(...)`.
- Does not yet support variable function calls.
- Does not yet support `call_user_func(...)` and friends,
  nor any other functions accepting callable parameters that may refer to functions.

## See also
- [dg/bypass-finals][1], which uses the same technique as this library.
- [PHP PreProcessor][2]
- [Patchwork][3]

[1]: https://github.com/dg/bypass-finals
[2]: https://github.com/ircmaxell/php-preprocessor
[3]: https://github.com/antecedent/patchwork
