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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Shift\ClassHook;

use Asmblah\PhpCodeShift\Shifter\Hook\ClassHooks;
use Asmblah\PhpCodeShift\Shifter\Shift\Spec\ShiftSpecInterface;

/**
 * Class ClassHookShiftSpec.
 *
 * Defines a shift that will hook the given PHP class, allowing a replacement
 * implementation to be substituted that is able to defer to the original as needed.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ClassHookShiftSpec implements ShiftSpecInterface
{
    /**
     * @param class-string $className
     * @param class-string $replacementClassName
     */
    public function __construct(
        private readonly string $className,
        private readonly string $replacementClassName
    ) {
    }

    /**
     * Fetches the name of the class to hook.
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * Fetches the replacement class name.
     *
     * @return class-string
     */
    public function getReplacementClassName(): string
    {
        return $this->replacementClassName;
    }

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        ClassHooks::installHook($this->className, $this->replacementClassName);
    }
}
