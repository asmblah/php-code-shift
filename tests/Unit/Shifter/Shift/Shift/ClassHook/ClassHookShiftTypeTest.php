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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter\Shift\Shift\ClassHook;

use Asmblah\PhpCodeShift\Shifter\Shift\Shift\ClassHook\ClassHookShiftSpec;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\ClassHook\ClassHookShiftType;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\ClassHook\ClassReferenceVisitor;
use Asmblah\PhpCodeShift\Shifter\Shift\Traverser\Ast\AstModificationTraverserInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery;
use Mockery\MockInterface;

/**
 * Class ClassHookShiftTypeTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ClassHookShiftTypeTest extends AbstractTestCase
{
    private MockInterface&AstModificationTraverserInterface $astTraverser;
    private MockInterface&ClassHookShiftSpec $shiftSpec;
    private ClassHookShiftType $shiftType;

    public function setUp(): void
    {
        $this->astTraverser = mock(AstModificationTraverserInterface::class);
        $this->shiftSpec = mock(ClassHookShiftSpec::class, [
            'getClassName' => 'My\Stuff\MyClass',
            'getReplacementClassName' => 'Your\Stuff\YourClass',
        ]);

        $this->shiftType = new ClassHookShiftType();
    }

    public function testConfigureTraversalAddsAClassReferenceVisitor(): void
    {
        $this->astTraverser->expects()
            ->addVisitor(Mockery::type(ClassReferenceVisitor::class))
            ->once();

        $this->shiftType->configureTraversal($this->shiftSpec, $this->astTraverser);
    }

    public function testGetShiftSpecFqcnReturnsCorrectFqcn(): void
    {
        static::assertSame(ClassHookShiftSpec::class, $this->shiftType->getShiftSpecFqcn());
    }
}
