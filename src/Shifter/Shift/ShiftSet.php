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

namespace Asmblah\PhpCodeShift\Shifter\Shift;

/**
 * Class ShiftSet.
 *
 * Represents a set of shifts to be applied to the given file path.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ShiftSet implements ShiftSetInterface
{
    /**
     * @param ShiftInterface[] $shifts
     */
    public function __construct(
        private string $path,
        private readonly array $shifts
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function getShifts(): array
    {
        return $this->shifts;
    }
}
