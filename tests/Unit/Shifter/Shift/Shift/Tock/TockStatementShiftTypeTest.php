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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter\Shift\Shift\Tock;

use Asmblah\PhpCodeShift\Shifter\Shift\Shift\Tock\TockSiteVisitor;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\Tock\TockStatementShiftSpec;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\Tock\TockStatementShiftType;
use Asmblah\PhpCodeShift\Shifter\Shift\Traverser\Ast\AstModificationTraverserInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery;
use Mockery\MockInterface;

/**
 * Class TockStatementShiftTypeTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class TockStatementShiftTypeTest extends AbstractTestCase
{
    private MockInterface&AstModificationTraverserInterface $astTraverser;
    private MockInterface&TockStatementShiftSpec $shiftSpec;
    private TockStatementShiftType $shiftType;

    public function setUp(): void
    {
        $this->astTraverser = mock(AstModificationTraverserInterface::class);
        $this->shiftSpec = mock(TockStatementShiftSpec::class);

        $this->shiftType = new TockStatementShiftType();
    }

    public function testConfigureTraversalAddsATockSiteVisitor(): void
    {
        $this->astTraverser->expects()
            ->addVisitor(Mockery::type(TockSiteVisitor::class))
            ->once();

        $this->shiftType->configureTraversal($this->shiftSpec, $this->astTraverser);
    }

    public function testGetShiftSpecFqcnReturnsCorrectFqcn(): void
    {
        static::assertSame(TockStatementShiftSpec::class, $this->shiftType->getShiftSpecFqcn());
    }
}
