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

namespace Asmblah\PhpCodeShift\Cache\Layer;

use Asmblah\PhpCodeShift\Cache\Adapter\CacheAdapterInterface;
use Asmblah\PhpCodeShift\Cache\Driver\CacheDriverInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Shifter\ShiftSetShifterInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Resolver\ShiftSetResolverInterface;
use Asmblah\PhpCodeShift\ShiftPackageInterface;
use Nytris\Core\Package\PackageContextInterface;

/**
 * Interface CacheLayerFactoryInterface.
 *
 * Abstracts the creation of the cache layer adapter and driver.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface CacheLayerFactoryInterface
{
    /**
     * Creates the cache adapter to use.
     */
    public function createCacheAdapter(PackageContextInterface $packageContext): CacheAdapterInterface;

    /**
     * Creates the cache driver to use.
     */
    public function createCacheDriver(
        CacheAdapterInterface $cacheAdapter,
        ShiftSetResolverInterface $shiftSetResolver,
        ShiftSetShifterInterface $shiftSetShifter,
        PackageContextInterface $packageContext,
        ShiftPackageInterface $package
    ): CacheDriverInterface;
}
