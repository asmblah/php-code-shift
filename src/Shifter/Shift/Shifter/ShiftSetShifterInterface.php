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
 * Interface ShiftSetShifterInterface.
 *
 * Applies a set of shifts represented by a ShiftSet to a code string.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface ShiftSetShifterInterface
{
    /**
     * Performs all shifts in the set against the given contents, returning the shifted result.
     */
    public function shift(string $contents, ShiftSetInterface $shiftSet): string;
}
