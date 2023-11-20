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
use Asmblah\PhpCodeShift\Shifter\Shift\Shifter\ShiftSetShifterInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Resolver\ShiftSetResolverInterface;

/**
 * Class StandaloneCacheProvider.
 *
 * Cache provider used when PHP Code Shift is installed standalone (not as a Nytris package).
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StandaloneCacheProvider implements CacheProviderInterface
{
    public function __construct(
        private readonly CacheAdapterInterface $cacheAdapter,
        private readonly CacheDriverInterface $cacheDriver
    ) {
    }

    /**
     * @inheritDoc
     */
    public function createCacheAdapter(): CacheAdapterInterface
    {
        return $this->cacheAdapter;
    }

    /**
     * @inheritDoc
     */
    public function createCacheDriver(
        CacheAdapterInterface $cacheAdapter,
        ShiftSetResolverInterface $shiftSetResolver,
        ShiftSetShifterInterface $shiftSetShifter
    ): CacheDriverInterface {
        return $this->cacheDriver;
    }
}
