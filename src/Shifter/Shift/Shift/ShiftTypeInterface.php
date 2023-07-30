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

/**
 * Interface ShiftTypeInterface.
 *
 * Represents a type of shift that may be defined via a shift spec.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface ShiftTypeInterface
{
    /**
     * Fetches the callable for this shift.
     */
    public function getShifter(): callable;

    /**
     * Fetches the FQCN of spec this shift uses.
     */
    public function getShiftSpecFqcn(): string;
}
