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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Context;

use Asmblah\PhpCodeShift\Shifter\Shift\ShiftSetInterface;

/**
 * Class ShiftContext.
 *
 * Represents the current context of the shift.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ShiftContext implements ShiftContextInterface
{
    public function __construct(
        private readonly ShiftSetInterface $shiftSet,
        private readonly string $contents
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getContents(): string
    {
        return $this->contents;
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return $this->shiftSet->getPath();
    }

    /**
     * @inheritDoc
     */
    public function getShifts(): array
    {
        return $this->shiftSet->getShifts();
    }
}
