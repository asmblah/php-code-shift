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

namespace Asmblah\PhpCodeShift\Shifter;

use Asmblah\PhpCodeShift\Shifter\Shift\ShiftInterface;

/**
 * Interface ShifterInterface.
 *
 * Wraps a collection of shifts that may be installed and applied.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface ShifterInterface
{
    public function addShift(ShiftInterface $shift): void;

    /**
     * Installs this shifter so that it takes effect.
     */
    public function install(): void;

    /**
     * Determines whether this shifter has been installed.
     */
    public function isInstalled(): bool;

    public function uninstall(): void;
}
