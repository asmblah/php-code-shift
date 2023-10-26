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

namespace Asmblah\PhpCodeShift\Shifter\Shift;

/**
 * Interface ShiftSetInterface.
 *
 * Represents a set of shifts to be applied to the given file path.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface ShiftSetInterface
{
    /**
     * Fetches the path this shift set applies to.
     */
    public function getPath(): string;

    /**
     * Fetches all shifts in the set.
     *
     * @return ShiftInterface[]
     */
    public function getShifts(): array;
}
