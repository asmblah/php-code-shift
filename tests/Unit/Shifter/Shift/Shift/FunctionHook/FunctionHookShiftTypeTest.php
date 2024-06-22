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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter\Shift\Shift\FunctionHook;

use Asmblah\PhpCodeShift\Shifter\Shift\Shift\FunctionHook\CallVisitor;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\FunctionHook\FunctionHookShiftSpec;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\FunctionHook\FunctionHookShiftType;
use Asmblah\PhpCodeShift\Shifter\Shift\Traverser\Ast\AstModificationTraverserInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery;
use Mockery\MockInterface;

/**
 * Class FunctionHookShiftTypeTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class FunctionHookShiftTypeTest extends AbstractTestCase
{
    private MockInterface&AstModificationTraverserInterface $astTraverser;
    private MockInterface&FunctionHookShiftSpec $shiftSpec;
    private FunctionHookShiftType $shiftType;

    public function setUp(): void
    {
        $this->astTraverser = mock(AstModificationTraverserInterface::class);
        $this->shiftSpec = mock(FunctionHookShiftSpec::class, [
            'getFunctionName' => 'myFunc',
        ]);

        $this->shiftType = new FunctionHookShiftType();
    }

    public function testConfigureTraversalAddsACallVisitor(): void
    {
        $this->astTraverser->expects()
            ->addVisitor(Mockery::type(CallVisitor::class))
            ->once();

        $this->shiftType->configureTraversal($this->shiftSpec, $this->astTraverser);
    }

    public function testGetShiftSpecFqcnReturnsCorrectFqcn(): void
    {
        static::assertSame(FunctionHookShiftSpec::class, $this->shiftType->getShiftSpecFqcn());
    }
}
