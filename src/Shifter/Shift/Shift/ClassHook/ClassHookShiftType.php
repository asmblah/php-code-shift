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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Shift\ClassHook;

use Asmblah\PhpCodeShift\Shifter\Shift\Shift\ShiftTypeInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Traverser\Ast\AstModificationTraverserInterface;

/**
 * Class ClassHookShiftType.
 *
 * Defines a shift that will hook the given PHP class, allowing a replacement
 * implementation to be substituted that is able to defer to the original as needed.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ClassHookShiftType implements ShiftTypeInterface
{
    /**
     * Configures the traversal for this shift.
     */
    public function configureTraversal(
        ClassHookShiftSpec $shiftSpec,
        AstModificationTraverserInterface $astTraverser
    ): void {
        $astTraverser->addVisitor(new ClassReferenceVisitor($shiftSpec));
    }

    /**
     * @inheritDoc
     */
    public function getConfigurer(): callable
    {
        return $this->configureTraversal(...);
    }

    /**
     * @inheritDoc
     */
    public function getShiftSpecFqcn(): string
    {
        return ClassHookShiftSpec::class;
    }
}
