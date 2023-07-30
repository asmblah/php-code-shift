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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Shift;

use Asmblah\PhpCodeShift\Shifter\Shift\Spec\ShiftSpecInterface;

/**
 * Interface DelegatingShiftInterface.
 *
 * Defines a list of shift types that may be applied and defers to the relevant one
 * based on the shift spec given.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface DelegatingShiftInterface
{
    /**
     * Registers the given type of shift.
     */
    public function registerShiftType(ShiftTypeInterface $shiftType): void;

    /**
     * Applies the relevant shift.
     */
    public function shift(ShiftSpecInterface $shiftSpec, string $contents): string;
}
