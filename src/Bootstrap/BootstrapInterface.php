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

namespace Asmblah\PhpCodeShift\Bootstrap;

use Asmblah\PhpCodeShift\Cache\CacheInterface;
use Asmblah\PhpCodeShift\Cache\Provider\CacheProviderInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Shifter\StreamShifterInterface;

/**
 * Interface BootstrapInterface.
 *
 * Bootstraps PHP Code Shift.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface BootstrapInterface
{
    /**
     * Fetches the cache.
     */
    public function getCache(): CacheInterface;

    /**
     * Fetches the stream shifter.
     */
    public function getStreamShifter(): StreamShifterInterface;

    /**
     * Installs PHP Code Shift.
     */
    public function install(CacheProviderInterface $cacheProvider): void;

    /**
     * Determines whether PHP Code Shift has been installed or not.
     */
    public function isInstalled(): bool;

    /**
     * Uninstalls PHP Code Shift.
     */
    public function uninstall(): void;
}
