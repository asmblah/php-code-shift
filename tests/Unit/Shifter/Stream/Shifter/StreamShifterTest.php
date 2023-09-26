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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter\Stream\Shifter;

use Asmblah\PhpCodeShift\Shifter\Shift\Shifter\ShiftSetShifterInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\ShiftSetInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Resolver\ShiftSetResolverInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Shifter\StreamShifter;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery\MockInterface;

/**
 * Class StreamShifterTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StreamShifterTest extends AbstractTestCase
{
    private MockInterface&ShiftSetInterface $resolvedShiftSet;
    /**
     * @var resource|null
     */
    private $resource;
    private MockInterface&ShiftSetShifterInterface $shiftSetShifter;
    private MockInterface&ShiftSetResolverInterface $shiftSetResolver;
    private StreamShifter $streamShifter;

    public function setUp(): void
    {
        $this->resolvedShiftSet = mock(ShiftSetInterface::class);
        $this->resource = fopen('php://memory', 'wb+');
        $this->shiftSetResolver = mock(ShiftSetResolverInterface::class, [
            'resolveShiftSet' => $this->resolvedShiftSet,
        ]);
        $this->shiftSetShifter = mock(ShiftSetShifterInterface::class);

        $this->streamShifter = new StreamShifter($this->shiftSetResolver, $this->shiftSetShifter);
    }

    public function testShiftReturnsResourceUnchangedWhenNoShiftsAreResolvedAsApplicable(): void
    {
        $this->shiftSetResolver->allows()
            ->resolveShiftSet('/my/path/to/my_module.php')
            ->andReturnNull();

        static::assertSame(
            $this->resource,
            $this->streamShifter->shift('/my/path/to/my_module.php', $this->resource)
        );
    }

    public function testShiftReturnsResourceUnchangedWhenAShiftIsAlreadyInProgress(): void
    {
        $firstResource = fopen('php://memory', 'wb+');
        fwrite($firstResource, '<?php "my original first contents";');
        rewind($firstResource);
        $firstShiftSet = mock(ShiftSetInterface::class);
        $this->shiftSetResolver->allows()
            ->resolveShiftSet('/my/first_module.php')
            ->andReturn($firstShiftSet);
        $secondResource = fopen('php://memory', 'wb+');
        fwrite($secondResource, '<?php "my original second contents";');
        rewind($secondResource);
        $secondShiftSet = mock(ShiftSetInterface::class);
        $this->shiftSetResolver->allows()
            ->resolveShiftSet('/my/second_module.php')
            ->andReturn($secondShiftSet);
        $secondShiftResult = null;
        $this->shiftSetShifter->allows()
            ->shift('<?php "my original first contents";', $firstShiftSet)
            ->andReturnUsing(function () use ($secondResource, &$secondShiftResult) {
                $secondShiftResult = $this->streamShifter->shift('/my/second_module.php', $secondResource);

                return '<?php "my new first contents";';
            });
        $this->shiftSetShifter->allows()
            ->shift('<?php "my original second contents";', $secondShiftSet)
            // Should not be used.
            ->andReturn('<?php "my new second contents";');

        $this->streamShifter->shift('/my/first_module.php', $firstResource);

        static::assertSame($secondResource, $secondShiftResult);
    }

    public function testShiftShiftsViaShiftSetShifter(): void
    {
        fwrite($this->resource, '<?php "my original contents";');
        rewind($this->resource);
        $this->shiftSetResolver->allows()
            ->resolveShiftSet('/my/path/to/my_module.php')
            ->andReturn($this->resolvedShiftSet);
        $this->shiftSetShifter->allows()
            ->shift('<?php "my original contents";', $this->resolvedShiftSet)
            ->andReturn('<?php "my new contents";');

        $shiftResult = $this->streamShifter->shift('/my/path/to/my_module.php', $this->resource);

        static::assertNotSame($this->resource, $shiftResult, 'A new stream should be returned');
        static::assertSame('<?php "my new contents";', stream_get_contents($shiftResult));
    }

    public function testShiftCanShiftMultipleFiles(): void
    {
        $firstResource = fopen('php://memory', 'wb+');
        fwrite($firstResource, '<?php "my original first contents";');
        rewind($firstResource);
        $firstShiftSet = mock(ShiftSetInterface::class);
        $this->shiftSetResolver->allows()
            ->resolveShiftSet('/my/first_module.php')
            ->andReturn($firstShiftSet);
        $secondResource = fopen('php://memory', 'wb+');
        fwrite($secondResource, '<?php "my original second contents";');
        rewind($secondResource);
        $secondShiftSet = mock(ShiftSetInterface::class);
        $this->shiftSetResolver->allows()
            ->resolveShiftSet('/my/second_module.php')
            ->andReturn($secondShiftSet);
        $secondShiftResult = null;
        $this->shiftSetShifter->allows()
            ->shift('<?php "my original first contents";', $firstShiftSet)
            ->andReturn('<?php "my new first contents";');
        $this->shiftSetShifter->allows()
            ->shift('<?php "my original second contents";', $secondShiftSet)
            ->andReturn('<?php "my new second contents";');

        $firstShiftResult = $this->streamShifter->shift('/my/first_module.php', $firstResource);
        $secondShiftResult = $this->streamShifter->shift('/my/second_module.php', $secondResource);

        static::assertNotSame($firstResource, $firstShiftResult, 'A new stream should be returned');
        static::assertSame('<?php "my new first contents";', stream_get_contents($firstShiftResult));
        static::assertNotSame($secondResource, $secondShiftResult, 'A new stream should be returned');
        static::assertSame('<?php "my new second contents";', stream_get_contents($secondShiftResult));
    }
}
