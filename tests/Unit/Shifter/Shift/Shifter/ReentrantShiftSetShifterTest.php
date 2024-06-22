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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter\Shift\Shifter;

use Asmblah\PhpCodeShift\Shifter\Shift\Shifter\ReentrantShiftSetShifter;
use Asmblah\PhpCodeShift\Shifter\Shift\Shifter\ShiftSetShifterInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\ShiftSetInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery\MockInterface;

/**
 * Class ReentrantShiftSetShifterTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ReentrantShiftSetShifterTest extends AbstractTestCase
{
    private ReentrantShiftSetShifter $shifter;
    private MockInterface&ShiftSetInterface $shiftSet;
    private MockInterface&ShiftSetShifterInterface $wrappedShifter;

    public function setUp(): void
    {
        $this->shiftSet = mock(ShiftSetInterface::class);
        $this->wrappedShifter = mock(ShiftSetShifterInterface::class);

        $this->shifter = new ReentrantShiftSetShifter($this->wrappedShifter);
    }

    public function testShiftReturnsShiftedContentsFromWrappedShifter(): void
    {
        $this->wrappedShifter->allows()
            ->shift('<?php 1234;', $this->shiftSet)
            ->andReturn('<?php 4321;');

        static::assertSame('<?php 4321;', $this->shifter->shift('<?php 1234;', $this->shiftSet));
    }

    public function testShiftReturnsContentsUnchangedWhenAShiftIsAlreadyInProgress(): void
    {
        $secondShiftResult = null;
        $this->wrappedShifter->allows()
            ->shift('<?php 1234;', $this->shiftSet)
            ->andReturnUsing(function () use (&$secondShiftResult) {
                $secondShiftResult = $this->shifter->shift('<?php 8888;', $this->shiftSet);

                return '<?php 4321;';
            });
        $this->wrappedShifter->allows()
            ->shift('<?php 8888;', $this->shiftSet)
            ->andReturn('<?php 4444;');
        $this->shifter->shift('<?php 1234;', $this->shiftSet);

        static::assertSame('<?php 8888;', $secondShiftResult, 'Contents should be unchanged');
    }
}
