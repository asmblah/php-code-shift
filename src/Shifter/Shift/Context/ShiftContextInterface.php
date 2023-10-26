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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Context;

use Asmblah\PhpCodeShift\Shifter\Shift\ShiftInterface;

/**
 * Interface ShiftContextInterface.
 *
 * Represents the current context of the shift.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface ShiftContextInterface extends ContextInterface
{
    /**
     * Fetches the original contents being shifted.
     */
    public function getContents(): string;

    /**
     * Fetches all shifts to be applied.
     *
     * @return ShiftInterface[]
     */
    public function getShifts(): array;
}
