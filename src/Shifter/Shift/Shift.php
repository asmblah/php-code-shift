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

use Asmblah\PhpCodeShift\Shifter\Filter\DenyListInterface;
use Asmblah\PhpCodeShift\Shifter\Filter\FileFilterInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Context\ShiftContextInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\DelegatingShiftInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Spec\ShiftSpecInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Traverser\Ast\AstModificationTraverserInterface;

/**
 * Class Shift.
 *
 * Represents a file filter and shift spec to apply to matching files.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Shift implements ShiftInterface
{
    public function __construct(
        private readonly DelegatingShiftInterface $delegatingShift,
        private readonly ShiftSpecInterface $shiftSpec,
        private readonly DenyListInterface $denyList,
        private readonly FileFilterInterface $fileFilter
    ) {
    }

    /**
     * @inheritDoc
     */
    public function appliesTo(string $path): bool
    {
        if ($this->denyList->fileMatches($path)) {
            // Never apply shifts to files in the deny list.
            return false;
        }

        return $this->fileFilter->fileMatches($path);
    }

    /**
     * @inheritDoc
     */
    public function configureTraversal(
        AstModificationTraverserInterface $astTraverser,
        ShiftContextInterface $shiftContext
    ): void {
        $this->delegatingShift->configureTraversal(
            $this->shiftSpec,
            $astTraverser,
            $shiftContext
        );
    }

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        $this->shiftSpec->init();
    }
}
