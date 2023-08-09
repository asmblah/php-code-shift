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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Shift\String;

use Asmblah\PhpCodeShift\Shifter\Shift\Shift\ShiftTypeInterface;

/**
 * Class StringShiftType.
 *
 * Defines a shift that will perform a string replacement.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StringShiftType implements ShiftTypeInterface
{
    /**
     * @inheritDoc
     */
    public function getShifter(): callable
    {
        return $this->shift(...);
    }

    /**
     * @inheritDoc
     */
    public function getShiftSpecFqcn(): string
    {
        return StringShiftSpec::class;
    }

    /**
     * Applies the shift to the contents.
     */
    public function shift(StringShiftSpec $shiftSpec, string $contents): string
    {
        return str_replace($shiftSpec->getNeedle(), $shiftSpec->getReplacement(), $contents);
    }
}
