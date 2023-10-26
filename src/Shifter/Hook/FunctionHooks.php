<?php

/*
 * PHP Code Shift - Monkey-patch PHP code on the fly.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/php-code-shift/
 *
 * Released under the MIT license.
 * https://github.com/asmblah/php-code-shift/raw/master/MIT-LICENSE.txt
 */

declare(strict_types=1);

namespace Asmblah\PhpCodeShift\Shifter\Hook;

/**
 * Class FunctionHooks.
 *
 * Stores function hook data, allowing the static API of the Invoker class to be kept clean
 * so that there will be no collisions with static method names used as replacements.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class FunctionHooks
{
    /**
     * @var array<string, callable>
     */
    private static array $hooks = [];

    /**
     * @param array<mixed> $args
     */
    public static function callFunction(string $functionName, array $args): mixed
    {
        return self::$hooks[$functionName](...$args);
    }

    public static function installHook(string $functionName, callable $implementation): void
    {
        self::$hooks[$functionName] = $implementation;
    }
}
