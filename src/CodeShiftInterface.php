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

namespace Asmblah\PhpCodeShift;

use Asmblah\PhpCodeShift\Shifter\Filter\FileFilterInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\ShiftTypeInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Spec\ShiftSpecInterface;
use OutOfBoundsException;

/**
 * Interface CodeShiftInterface.
 *
 * Defines the public facade API for the library.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface CodeShiftInterface
{
    /**
     * Adds a filter for paths that should never be transpiled.
     */
    public function deny(FileFilterInterface $filter): void;

    /**
     * Excludes a Composer package from being transpiled.
     *
     * @throws OutOfBoundsException When the package is not installed.
     */
    public function excludeComposerPackage(string $packageName): void;

    /**
     * Excludes a Composer package from being transpiled.
     *
     * Does nothing if the package is not installed.
     */
    public function excludeComposerPackageIfInstalled(string $packageName): void;

    /**
     * Installs all behaviour registered by this CodeShift instance.
     */
    public function install(): void;

    /**
     * Registers the given type of shift.
     */
    public function registerShiftType(ShiftTypeInterface $shiftType): void;

    /**
     * Adds the specified shift to be applied when applicable.
     */
    public function shift(ShiftSpecInterface $shiftSpec, ?FileFilterInterface $fileFilter = null): void;

    /**
     * Uninstalls all behaviour registered by this CodeShift instance.
     */
    public function uninstall(): void;
}
