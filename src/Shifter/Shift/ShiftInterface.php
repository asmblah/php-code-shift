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

namespace Asmblah\PhpCodeShift\Shifter\Shift;

use Asmblah\PhpCodeShift\Shifter\Shift\Context\ShiftContextInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Traverser\Ast\AstModificationTraverserInterface;

/**
 * Interface ShiftInterface.
 *
 * Represents a file filter and shift spec to apply to matching files.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface ShiftInterface
{
    /**
     * Determines whether this shift applies to the given path.
     */
    public function appliesTo(string $path): bool;

    /**
     * Configures the traversal for the shift.
     */
    public function configureTraversal(
        AstModificationTraverserInterface $astTraverser,
        ShiftContextInterface $shiftContext
    ): void;

    /**
     * Performs any initialisation needed for the shift.
     */
    public function init(): void;
}
