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
use Psr\Log\LoggerInterface;

/**
 * Interface CacheProviderInterface.
 *
 * Abstracts the creation of the cache layer adapter and driver
 * depending on the way in which PHP Code Shift is installed.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface CacheProviderInterface
{
    /**
     * Creates the cache adapter.
     */
    public function createCacheAdapter(): CacheAdapterInterface;

    /**
     * Creates the cache driver.
     */
    public function createCacheDriver(
        CacheAdapterInterface $cacheAdapter,
        ShiftSetResolverInterface $shiftSetResolver,
        ShiftSetShifterInterface $shiftSetShifter,
        LoggerInterface $logger
    ): CacheDriverInterface;
}
