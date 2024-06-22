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

use Asmblah\PhpCodeShift\Cache\Layer\CacheLayerFactoryInterface;
use Nytris\Core\Package\PackageInterface;

/**
 * Interface ShiftPackageInterface.
 *
 * Configures the installation of PHP Code Shift.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface ShiftPackageInterface extends PackageInterface
{
    public const DEFAULT_SOURCE_PATTERN = '/^.+\.php$/i';

    /**
     * Fetches the cache layer factory to use.
     */
    public function getCacheLayerFactory(): CacheLayerFactoryInterface;

    /**
     * Fetches the source code paths relative to the project root to warm into the cache.
     *
     * @return string[]
     */
    public function getRelativeSourcePaths(): array;

    /**
     * Fetches the regex pattern to filter source files to be compiled by.
     */
    public function getSourcePattern(): string;

    /**
     * Whether to check original files' modification timestamps to detect when cache files are stale.
     */
    public function validateTimestamps(): bool;
}
