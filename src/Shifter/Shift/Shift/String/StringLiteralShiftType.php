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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Shift\String;

use Asmblah\PhpCodeShift\Shifter\Shift\Shift\ShiftTypeInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Traverser\Ast\AstModificationTraverserInterface;

/**
 * Class StringLiteralShiftType.
 *
 * Defines a shift that will perform a string replacement.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StringLiteralShiftType implements ShiftTypeInterface
{
    /**
     * Configures the traversal for this shift.
     */
    public function configureTraversal(
        StringLiteralShiftSpec $shiftSpec,
        AstModificationTraverserInterface $astTraverser
    ): void {
        $astTraverser->addVisitor(new StringLiteralVisitor($shiftSpec));
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
        return StringLiteralShiftSpec::class;
    }
}
