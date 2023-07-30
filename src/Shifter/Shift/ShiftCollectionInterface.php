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
 * Interface ShiftCollectionInterface.
 *
 * Contains a set of shifts as registered by a given Shifter.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface ShiftCollectionInterface
{
    public function addShift(ShiftInterface $shift): void;

    /**
     * Fetches all shifts in the collection.
     *
     * @return ShiftInterface[]
     */
    public function getShifts(): array;
}
