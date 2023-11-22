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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Shifter;

use Asmblah\PhpCodeShift\Shifter\Shift\ShiftSetInterface;

/**
 * Class ReentrantShiftSetShifter.
 *
 * Applies a set of shifts represented by a ShiftSet to a code string,
 * preventing any shifting of autoloaded classes from occurring during the process
 * in order to avoid errors due to a recursive dependency.
 *
 * e.g. `Error: Class "PhpParser\ErrorHandler\Throwing" not found`.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ReentrantShiftSetShifter implements ShiftSetShifterInterface
{
    private bool $shifting = false;

    public function __construct(
        private readonly ShiftSetShifterInterface $wrappedShifter
    ) {
    }

    /**
     * @inheritDoc
     */
    public function shift(string $contents, ShiftSetInterface $shiftSet): string
    {
        if ($this->shifting) {
            /*
             * Don't attempt to perform shifts while we're already in the process
             * of shifting a file, to prevent recursion.
             */
            return $contents;
        }

        $this->shifting = true;

        try {
            return $this->wrappedShifter->shift($contents, $shiftSet);
        } finally {
            $this->shifting = false;
        }
    }
}
