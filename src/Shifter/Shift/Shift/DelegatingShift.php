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

use Asmblah\PhpCodeShift\Shifter\Shift\Spec\ShiftSpecInterface;
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
    private array $shiftSpecFqcnToShifterCallable = [];

    /**
     * @inheritDoc
     */
    public function registerShiftType(ShiftTypeInterface $shiftType): void
    {
        $this->shiftSpecFqcnToShifterCallable[$shiftType->getShiftSpecFqcn()] = $shiftType->getShifter();
    }

    /**
     * @inheritDoc
     */
    public function shift(ShiftSpecInterface $shiftSpec, string $contents): string
    {
        if (!array_key_exists($shiftSpec::class, $this->shiftSpecFqcnToShifterCallable)) {
            throw new InvalidArgumentException(
                sprintf(
                    '%s :: No shift registered for spec of type %s',
                    __METHOD__,
                    $shiftSpec::class
                )
            );
        }

        return $this->shiftSpecFqcnToShifterCallable[$shiftSpec::class]($shiftSpec, $contents);
    }
}
