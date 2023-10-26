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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter\Shift;

use Asmblah\PhpCodeShift\Shifter\Shift\ShiftCollection;
use Asmblah\PhpCodeShift\Shifter\Shift\ShiftInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;

/**
 * Class ShiftCollectionTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ShiftCollectionTest extends AbstractTestCase
{
    private ShiftCollection $collection;

    public function setUp(): void
    {
        $this->collection = new ShiftCollection();
    }

    public function testAddShiftAddsTheGivenShift(): void
    {
        $shift1 = mock(ShiftInterface::class);
        $this->collection->addShift($shift1);
        $shift2 = mock(ShiftInterface::class);
        $this->collection->addShift($shift2);

        static::assertSame([$shift1, $shift2], $this->collection->getShifts());
    }

    public function testClearRemovesAllAddedShifts(): void
    {
        $shift = mock(ShiftInterface::class);
        $this->collection->addShift($shift);

        $this->collection->clear();

        static::assertEmpty($this->collection->getShifts());
    }
}
