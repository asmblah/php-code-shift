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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Shift;

use Asmblah\PhpCodeShift\Shifter\Shift\Context\ShiftContextInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Spec\ShiftSpecInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Traverser\Ast\AstModificationTraverserInterface;

/**
 * Interface DelegatingShiftInterface.
 *
 * Defines a list of shift types that may be applied and defers to the relevant one
 * based on the shift spec given.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface DelegatingShiftInterface
{
    /**
     * Configures the traversal for the relevant shift.
     */
    public function configureTraversal(
        ShiftSpecInterface $shiftSpec,
        AstModificationTraverserInterface $astTraverser,
        ShiftContextInterface $shiftContext
    ): void;

    /**
     * Registers the given type of shift.
     */
    public function registerShiftType(ShiftTypeInterface $shiftType): void;
}
