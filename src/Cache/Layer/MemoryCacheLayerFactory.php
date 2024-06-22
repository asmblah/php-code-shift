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

namespace Asmblah\PhpCodeShift\Cache\Layer;

use Asmblah\PhpCodeShift\Cache\Adapter\CacheAdapterInterface;
use Asmblah\PhpCodeShift\Cache\Adapter\MemoryCacheAdapter;
use Asmblah\PhpCodeShift\Cache\Driver\CacheDriverInterface;
use Asmblah\PhpCodeShift\Cache\Driver\NullCacheDriver;
use Asmblah\PhpCodeShift\Shifter\Shift\Shifter\ShiftSetShifterInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Resolver\ShiftSetResolverInterface;
use Asmblah\PhpCodeShift\ShiftPackageInterface;
use Nytris\Core\Package\PackageContextInterface;
use Psr\Log\LoggerInterface;

/**
 * Class MemoryCacheLayerFactory.
 *
 * Abstracts the creation of the cache layer adapter and driver.
 * Used when there is no need to cache shifted code on disk to speed up future execution.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class MemoryCacheLayerFactory implements CacheLayerFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createCacheAdapter(PackageContextInterface $packageContext): CacheAdapterInterface
    {
        return new MemoryCacheAdapter();
    }

    /**
     * @inheritDoc
     */
    public function createCacheDriver(
        CacheAdapterInterface $cacheAdapter,
        ShiftSetResolverInterface $shiftSetResolver,
        ShiftSetShifterInterface $shiftSetShifter,
        LoggerInterface $logger,
        PackageContextInterface $packageContext,
        ShiftPackageInterface $package
    ): CacheDriverInterface {
        return new NullCacheDriver();
    }
}
