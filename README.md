# PHP Code Shift.

[![Build Status](https://github.com/asmblah/php-code-shift/workflows/CI/badge.svg)](https://github.com/asmblah/php-code-shift/actions?query=workflow%3ACI)

Allows running PHP code to be monkey-patched either ahead of time or on the fly.

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
// NB: substr(...) will be hooked by the shift defined inside runner.php.
$myResult = substr('my string', 1, 4) . ' and ' . substr('your string', 1, 2);

print $myResult;
```

The output will be:
```
[substr<y st>] and [substr<ou>]
```

### Hooking classes

References to a class may be replaced with references to a different class. This only works
for statically-referenced classes, i.e. where it is referenced with a bareword, e.g. `new MyClass`.

Dynamic/variable references are not supported, e.g. `new $myClassName` as they can only be resolved at runtime.

Any matching types are _not_ replaced - the replacement class must extend the original class or interface
in order to pass type checks.

`runner.php`

```php
<?php

use Asmblah\PhpCodeShift\CodeShift;
use Asmblah\PhpCodeShift\Shifter\Filter\FileFilter;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\ClassHook\ClassHookShiftSpec;

require_once __DIR__ . '/vendor/autoload.php';

$codeShift = new CodeShift();

$codeShift->shift(
    new ClassHookShiftSpec(
        'MyClass',
        'MyReplacementClass'
    ),
    new FileFilter(__DIR__ . '/class_test.php')
);

include __DIR__ . '/class_test.php';
```

`class_test.php`
```php
<?php

class MyClass
{
    public function getIt(): string
    {
        return 'my original string';
    }
}

class MyReplacementClass
{
    public function getIt(): string
    {
        return 'my replacement string';
    }
}

// NB: References to MyClass will be hooked by the shift defined inside runner.php.
$myObject = new MyClass;

print $myObject->getIt();
```

The output will be:
```
my replacement string
```

Static method calls (`MyClass::myStaticMethod()`) and class constant fetches (`MyClass:MY_CONST`) are also supported.

## Limitations
Functionality is extremely limited at the moment, you may well be better off using one of the alternatives
listed in [See also](#see-also) below.

- Does not yet support `eval(...)`.
- `FunctionHookShiftType` does not yet support variable function calls.
- `FunctionHookShiftType` does not yet support `call_user_func(...)` and friends,
  nor any other functions accepting callable parameters that may refer to functions.

## See also
- [dg/bypass-finals][1], which uses the same technique as this library.
- [PHP PreProcessor][2]
- [Patchwork][3]

[1]: https://github.com/dg/bypass-finals
[2]: https://github.com/ircmaxell/php-preprocessor
[3]: https://github.com/antecedent/patchwork
