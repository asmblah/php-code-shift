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
 * Class ClassHooks.
 *
 * Stores a mapping from the original class to its replacement class name.
 * This allows anonymous classes, whose names contain special characters, to be used as replacements.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ClassHooks
{
    /**
     * @var array<class-string, class-string>
     */
    private static array $hooks = [];

    /**
     * Clears all installed class hooks.
     */
    public static function clear(): void
    {
        self::$hooks = [];
    }

    /**
     * Fetches a hooked class' replacement.
     *
     * @param class-string $className
     * @return class-string
     */
    public static function getReplacement(string $className): string
    {
        return self::$hooks[$className];
    }

    /**
     * Installs a new hook for a class.
     *
     * @param class-string $className
     * @param class-string $replacementClassName
     */
    public static function installHook(string $className, string $replacementClassName): void
    {
        self::$hooks[$className] = $replacementClassName;
    }
}
