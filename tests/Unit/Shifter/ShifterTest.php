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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter;

use Asmblah\PhpCodeShift\Shifter\Shift\ShiftCollectionInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\ShiftInterface;
use Asmblah\PhpCodeShift\Shifter\Shifter;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery\MockInterface;

/**
 * Class ShifterTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ShifterTest extends AbstractTestCase
{
    private MockInterface&ShiftCollectionInterface $shiftCollection;
    private Shifter $shifter;

    public function setUp(): void
    {
        $this->shiftCollection = mock(ShiftCollectionInterface::class, [
            'addShift' => null,
            'clear' => null,
            'getShifts' => [],
        ]);

        $this->shifter = new Shifter($this->shiftCollection);
    }

    public function testAddShiftAddsTheShiftToTheCollection(): void
    {
        $shift = mock(ShiftInterface::class, [
            'init' => null,
        ]);

        $this->shiftCollection->expects()
            ->addShift($shift)
            ->once();

        $this->shifter->addShift($shift);
    }

    public function testAddShiftInitialisesTheShift(): void
    {
        $shift = mock(ShiftInterface::class);

        $shift->expects()
            ->init()
            ->once();

        $this->shifter->addShift($shift);
    }

    public function testInstallMarksShifterAsInstalled(): void
    {
        $this->shifter->install();

        static::assertTrue($this->shifter->isInstalled());
    }

    public function testIsInstalledReturnsFalseInitially(): void
    {
        static::assertFalse($this->shifter->isInstalled());
    }

    public function testUninstallMarksShifterAsUninstalled(): void
    {
        $this->shifter->install();

        $this->shifter->uninstall();

        static::assertFalse($this->shifter->isInstalled());
    }

    public function testUninstallClearsShiftCollection(): void
    {
        $this->shifter->install();

        $this->shiftCollection->expects()
            ->clear()
            ->once();

        $this->shifter->uninstall();
    }
}
