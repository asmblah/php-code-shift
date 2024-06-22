<?php

/*
 * PHP Code Shift - Monkey-patch PHP code on the fly.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/php-code-shift/
 *
 * Released under the MIT license.
 * https://github.com/asmblah/php-code-shift/raw/main/MIT-LICENSE.txt
 */

declare(strict_types=1);

namespace Asmblah\PhpCodeShift\Shifter\Hook;

/**
 * Class Invoker.
 *
 * Target for transpiled hooked function calls.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Invoker
{
    /**
     * @param array<mixed> $args
     */
    public static function __callStatic(string $name, array $args): mixed
    {
        return FunctionHooks::callFunction($name, $args);
    }
}
