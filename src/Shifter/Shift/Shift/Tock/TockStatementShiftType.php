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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Shift\Tock;

use Asmblah\PhpCodeShift\Shifter\Shift\Shift\ShiftTypeInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Traverser\Ast\AstModificationTraverserInterface;

/**
 * Class TockStatementShiftType.
 *
 * Defines a shift that will add a statement at the entry of userland functions
 * and top of loop bodies.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class TockStatementShiftType implements ShiftTypeInterface
{
    /**
     * Configures the traversal for this shift.
     */
    public function configureTraversal(
        TockStatementShiftSpec $shiftSpec,
        AstModificationTraverserInterface $astTraverser
    ): void {
        $astTraverser->addVisitor(new TockSiteVisitor($shiftSpec));
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
        return TockStatementShiftSpec::class;
    }
}
