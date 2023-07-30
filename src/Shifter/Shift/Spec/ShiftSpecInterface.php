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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Spec;

/**
 * Interface ShiftSpecInterface.
 *
 * Defines a shift of a particular type that will be applied, including its configuration.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface ShiftSpecInterface
{
    /**
     * Fetches the FQCN of the shift to apply.
     *
     * @return string
     */
    public function getShiftFqcn(): string;

    /**
     * Performs any initialisation needed for the shift.
     */
    public function init(): void;
}
