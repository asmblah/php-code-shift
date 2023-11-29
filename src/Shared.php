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

namespace Asmblah\PhpCodeShift;

use Asmblah\PhpCodeShift\Bootstrap\Bootstrap;
use Asmblah\PhpCodeShift\Bootstrap\BootstrapInterface;
use Asmblah\PhpCodeShift\Cache\Adapter\MemoryCacheAdapter;
use Asmblah\PhpCodeShift\Cache\Driver\NullCacheDriver;
use Asmblah\PhpCodeShift\Cache\Provider\StandaloneCacheProvider;
use Asmblah\PhpCodeShift\Shifter\Stream\Shifter\StreamShifterInterface;
use Asmblah\PhpCodeShift\Util\CallStack;
use Asmblah\PhpCodeShift\Util\CallStackInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Shared.
 *
 * Manages all services shared between instances of PHP Code Shift.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Shared
{
    private static ?BootstrapInterface $bootstrap;
    private static ?CallStackInterface $callStack;
    private static bool $initialised = false;
    private static ?LoggerInterface $logger;

    /**
     * Initialises PHP Code Shift early on, so that it may be used as early as possible.
     */
    public static function initialise(): void
    {
        if (self::$initialised) {
            return;
        }

        self::$initialised = true;

        self::$bootstrap = new Bootstrap();
        self::$callStack = new CallStack();
        self::$logger = new NullLogger();
    }

    /**
     * Fetches the Bootstrap service.
     */
    public static function getBootstrap(): BootstrapInterface
    {
        return self::$bootstrap;
    }

    /**
     * Fetches the CallStack service.
     */
    public static function getCallStack(): CallStackInterface
    {
        return self::$callStack;
    }

    /**
     * Fetches the Logger service.
     */
    public static function getLogger(): LoggerInterface
    {
        return self::$logger;
    }

    /**
     * Fetches the StreamShifter service.
     */
    public static function getStreamShifter(): StreamShifterInterface
    {
        if (!self::$bootstrap->isInstalled()) {
            /*
             * Nytris package was not installed - fall back to memory cache.
             * Note that this will prevent a later install of PHP Code Shift as a Nytris package.
             */
            self::$bootstrap->install(new StandaloneCacheProvider(new MemoryCacheAdapter(), new NullCacheDriver()));
        }

        return self::$bootstrap->getStreamShifter();
    }

    /**
     * Installs a new Bootstrap.
     */
    public static function setBootstrap(BootstrapInterface $bootstrap): void
    {
        self::$bootstrap = $bootstrap;
    }

    /**
     * Installs a new CallStack.
     */
    public static function setCallStack(CallStackInterface $callStack): void
    {
        self::$callStack = $callStack;
    }

    /**
     * Installs a new Logger.
     */
    public static function setLogger(LoggerInterface $logger): void
    {
        self::$logger = $logger;
    }

    /**
     * Uninitialises PHP Code Shift. Only really useful for testing.
     */
    public static function uninitialise(): void
    {
        self::$bootstrap = null;
        self::$callStack = null;
        self::$initialised = false;
    }
}
