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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter\Shift\Shift;

use Asmblah\PhpCodeShift\Shifter\Shift\Context\ShiftContextInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\DelegatingShift;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\ShiftTypeInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Spec\ShiftSpecInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Traverser\Ast\AstModificationTraverserInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use InvalidArgumentException;
use Mockery\MockInterface;

/**
 * Class DelegatingShiftTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class DelegatingShiftTest extends AbstractTestCase
{
    private MockInterface&AstModificationTraverserInterface $astTraverser;
    private DelegatingShift $delegatingShift;
    private MockInterface&ShiftContextInterface $shiftContext;

    public function setUp(): void
    {
        $this->astTraverser = mock(AstModificationTraverserInterface::class);
        $this->shiftContext = mock(ShiftContextInterface::class);

        $this->delegatingShift = new DelegatingShift();
    }

    public function testConfigureTraversalThrowsWhenNoShiftIsRegisteredForSpec(): void
    {
        $spec = mock(ShiftSpecInterface::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(':: No shift registered for spec of type ' . $spec::class);

        $this->delegatingShift->configureTraversal($spec, $this->astTraverser, $this->shiftContext);
    }

    public function testConfigureTraversalInvokesTheConfigurerCallableForTheShift(): void
    {
        $shiftSpec = mock(ShiftSpecInterface::class);
        $shiftType = mock(ShiftTypeInterface::class);
        $configurerCallable = spy();
        $shiftType->allows('getConfigurer')
            ->andReturn($configurerCallable->__invoke(...));
        $shiftType->allows('getShiftSpecFqcn')
            ->andReturn($shiftSpec::class);
        $this->delegatingShift->registerShiftType($shiftType);

        $configurerCallable->expects()
            ->__invoke($shiftSpec, $this->astTraverser, $this->shiftContext)
            ->once();

        $this->delegatingShift->configureTraversal($shiftSpec, $this->astTraverser, $this->shiftContext);
    }
}
