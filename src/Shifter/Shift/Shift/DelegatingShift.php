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
use Asmblah\PhpCodeShift\Shifter\Shift\Traverser\AstTraverserInterface;
use InvalidArgumentException;

/**
 * Class DelegatingShift.
 *
 * Defines a list of shift types that may be applied and defers to the relevant one
 * based on the shift spec given.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class DelegatingShift implements DelegatingShiftInterface
{
    /**
     * @var array<string, callable>
     */
    private array $shiftSpecFqcnToConfigurerCallable = [];

    /**
     * @inheritDoc
     */
    public function configureTraversal(
        ShiftSpecInterface $shiftSpec,
        AstTraverserInterface $astTraverser,
        ShiftContextInterface $shiftContext
    ): void {
        if (!array_key_exists($shiftSpec::class, $this->shiftSpecFqcnToConfigurerCallable)) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s :: No shift registered for spec of type %s',
                    __METHOD__,
                    $shiftSpec::class
                )
            );
        }

        $configurerCallable = $this->shiftSpecFqcnToConfigurerCallable[$shiftSpec::class];

        $configurerCallable($shiftSpec, $astTraverser, $shiftContext);
    }

    /**
     * @inheritDoc
     */
    public function registerShiftType(ShiftTypeInterface $shiftType): void
    {
        $this->shiftSpecFqcnToConfigurerCallable[$shiftType->getShiftSpecFqcn()] = $shiftType->getConfigurer();
    }
}
