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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter\Shift\Shift\String;

use Asmblah\PhpCodeShift\Shifter\Shift\Shift\String\StringLiteralShiftSpec;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\String\StringLiteralShiftType;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\String\StringLiteralVisitor;
use Asmblah\PhpCodeShift\Shifter\Shift\Traverser\Ast\AstModificationTraverserInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery;
use Mockery\MockInterface;

/**
 * Class StringLiteralShiftTypeTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StringLiteralShiftTypeTest extends AbstractTestCase
{
    private MockInterface&AstModificationTraverserInterface $astTraverser;
    private MockInterface&StringLiteralShiftSpec $shiftSpec;
    private StringLiteralShiftType $shiftType;

    public function setUp(): void
    {
        $this->astTraverser = mock(AstModificationTraverserInterface::class);
        $this->shiftSpec = mock(StringLiteralShiftSpec::class, [
            'getNeedle' => 'my needle',
            'getReplacement' => 'my replacement',
        ]);

        $this->shiftType = new StringLiteralShiftType();
    }

    public function testConfigureTraversalAddsAStringLiteralVisitor(): void
    {
        $this->astTraverser->expects()
            ->addVisitor(Mockery::type(StringLiteralVisitor::class))
            ->once();

        $this->shiftType->configureTraversal($this->shiftSpec, $this->astTraverser);
    }

    public function testGetShiftSpecFqcnReturnsCorrectFqcn(): void
    {
        static::assertSame(StringLiteralShiftSpec::class, $this->shiftType->getShiftSpecFqcn());
    }
}
