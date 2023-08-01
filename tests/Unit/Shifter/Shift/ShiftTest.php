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

use Asmblah\PhpCodeShift\Shifter\Filter\DenyListInterface;
use Asmblah\PhpCodeShift\Shifter\Filter\FileFilterInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\DelegatingShiftInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Spec\ShiftSpecInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery\MockInterface;

/**
 * Class ShiftTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ShiftTest extends AbstractTestCase
{
    /**
     * @var (MockInterface&DelegatingShiftInterface)|null
     */
    private $delegatingShift;
    /**
     * @var (MockInterface&DenyListInterface)|null
     */
    private $denyList;
    /**
     * @var (MockInterface&FileFilterInterface)|null
     */
    private $fileFilter;
    private ?Shift $shift;
    /**
     * @var (MockInterface&ShiftSpecInterface)|null
     */
    private $shiftSpec;

    public function setUp(): void
    {
        $this->delegatingShift = mock(DelegatingShiftInterface::class);
        $this->denyList = mock(DenyListInterface::class);
        $this->fileFilter = mock(FileFilterInterface::class);
        $this->shiftSpec = mock(ShiftSpecInterface::class);

        $this->shift = new Shift(
            $this->delegatingShift,
            $this->shiftSpec,
            $this->denyList,
            $this->fileFilter
        );
    }

    public function testAppliesToReturnsTrueWhenTheDenyListDoesNotBlockThePathAndFilterAllows(): void
    {
        $this->denyList->allows()
            ->fileMatches('/my/path.php')
            ->andReturn(false);
        $this->fileFilter->allows()
            ->fileMatches('/my/path.php')
            ->andReturn(true);

        static::assertTrue($this->shift->appliesTo('/my/path.php'));
    }

    public function testAppliesToReturnsFalseWhenTheDenyListBlocksThePathAlthoughFilterWouldAllow(): void
    {
        $this->denyList->allows()
            ->fileMatches('/my/path.php')
            ->andReturn(true);
        $this->fileFilter->allows()
            ->fileMatches('/my/path.php')
            ->andReturn(true);

        static::assertFalse($this->shift->appliesTo('/my/path.php'));
    }

    public function testInitInitsTheShiftSpec(): void
    {
        $this->shiftSpec->expects()
            ->init()
            ->once();

        $this->shift->init();
    }

    public function testShiftShiftsViaTheDelegatingShift(): void
    {
        $this->delegatingShift->allows()
            ->shift($this->shiftSpec, 'my contents')
            ->andReturn('my shifted contents');

        static::assertSame('my shifted contents', $this->shift->shift('my contents'));
    }
}
