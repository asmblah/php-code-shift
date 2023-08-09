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

namespace Asmblah\PhpCodeShift;

use Asmblah\PhpCodeShift\Shifter\Filter\FileFilterInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\ShiftTypeInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Spec\ShiftSpecInterface;

/**
 * Interface CodeShiftFacadeInterface.
 *
 * Defines the public facade API for the library.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface CodeShiftFacadeInterface
{
    /**
     * Adds a filter for paths that should never be transpiled.
     */
    public function deny(FileFilterInterface $filter): void;

    /**
     * Registers the given type of shift.
     */
    public function registerShiftType(ShiftTypeInterface $shiftType): void;

    /**
     * Adds the specified shift to be applied when applicable.
     */
    public function shift(ShiftSpecInterface $shiftSpec, ?FileFilterInterface $fileFilter = null): void;

    /**
     * Uninstalls all shifts registered by this CodeShift instance.
     */
    public function uninstall(): void;
}
