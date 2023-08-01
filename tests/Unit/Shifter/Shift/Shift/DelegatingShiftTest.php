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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter\Shift\Shift;

use Asmblah\PhpCodeShift\Shifter\Shift\Shift\DelegatingShift;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\ShiftTypeInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Spec\ShiftSpecInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use InvalidArgumentException;

/**
 * Class DelegatingShiftTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class DelegatingShiftTest extends AbstractTestCase
{
    private ?DelegatingShift $delegatingShift;

    public function setUp(): void
    {
        $this->delegatingShift = new DelegatingShift();
    }

    public function testShiftThrowsWhenNoShiftIsRegisteredForSpec(): void
    {
        $spec = mock(ShiftSpecInterface::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(':: No shift registered for spec of type ' . $spec::class);

        $this->delegatingShift->shift($spec, 'my contents');
    }

    public function testShiftReturnsContentsUnchangedWhenAlreadyShifting(): void
    {
        $shiftSpec = mock(ShiftSpecInterface::class);
        $shiftType = mock(ShiftTypeInterface::class);
        $shiftType->allows('getShifter')
            ->andReturn(function () {
                $secondShiftSpec = mock(ShiftSpecInterface::class);

                return $this->delegatingShift->shift($secondShiftSpec, 'second contents');
            });
        $shiftType->allows('getShiftSpecFqcn')
            ->andReturn($shiftSpec::class);
        $this->delegatingShift->registerShiftType($shiftType);

        static::assertSame(
            'second contents',
            $this->delegatingShift->shift($shiftSpec, 'my contents')
        );
    }
}
