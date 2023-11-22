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

namespace Asmblah\PhpCodeShift\Cache\Provider;

use Asmblah\PhpCodeShift\Cache\Adapter\CacheAdapterInterface;
use Asmblah\PhpCodeShift\Cache\Driver\CacheDriverInterface;
use Asmblah\PhpCodeShift\Cache\Layer\CacheLayerFactoryInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Shifter\ShiftSetShifterInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Resolver\ShiftSetResolverInterface;
use Asmblah\PhpCodeShift\ShiftPackageInterface;
use Nytris\Core\Package\PackageContextInterface;

/**
 * Class PackageCacheProvider.
 *
 * Cache provider used when PHP Code Shift is installed as a Nytris package.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class PackageCacheProvider implements CacheProviderInterface
{
    public function __construct(
        private readonly CacheLayerFactoryInterface $cacheLayerFactory,
        private readonly PackageContextInterface $packageContext,
        private readonly ShiftPackageInterface $package
    ) {
    }

    /**
     * @inheritDoc
     */
    public function createCacheAdapter(): CacheAdapterInterface
    {
        return $this->cacheLayerFactory->createCacheAdapter($this->packageContext);
    }

    /**
     * @inheritDoc
     */
    public function createCacheDriver(
        CacheAdapterInterface $cacheAdapter,
        ShiftSetResolverInterface $shiftSetResolver,
        ShiftSetShifterInterface $shiftSetShifter
    ): CacheDriverInterface {
        return $this->cacheLayerFactory->createCacheDriver(
            $cacheAdapter,
            $shiftSetResolver,
            $shiftSetShifter,
            $this->packageContext,
            $this->package
        );
    }
}
