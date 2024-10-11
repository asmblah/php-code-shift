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

use Asmblah\PhpCodeShift\Bootstrap\Bootstrap;
use Asmblah\PhpCodeShift\Bootstrap\BootstrapInterface;
use Asmblah\PhpCodeShift\Cache\Adapter\MemoryCacheAdapter;
use Asmblah\PhpCodeShift\Cache\Driver\NullCacheDriver;
use Asmblah\PhpCodeShift\Cache\Provider\StandaloneCacheProvider;
use Asmblah\PhpCodeShift\Environment\Environment;
use Asmblah\PhpCodeShift\Environment\EnvironmentInterface;
use Asmblah\PhpCodeShift\Filesystem\Access\AccessResolver;
use Asmblah\PhpCodeShift\Filesystem\Stat\AclStatResolver;
use Asmblah\PhpCodeShift\Filesystem\Stat\NativeStatResolver;
use Asmblah\PhpCodeShift\Filesystem\Stat\StatResolverInterface;
use Asmblah\PhpCodeShift\Logger\DelegatingLogger;
use Asmblah\PhpCodeShift\Logger\DelegatingLoggerInterface;
use Asmblah\PhpCodeShift\Posix\CachingPosix;
use Asmblah\PhpCodeShift\Posix\Posix;
use Asmblah\PhpCodeShift\Shifter\Stream\Shifter\StreamShifterInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Unwrapper\Unwrapper;
use Asmblah\PhpCodeShift\Shifter\Stream\Unwrapper\UnwrapperInterface;
use Asmblah\PhpCodeShift\Util\CallStack;
use Asmblah\PhpCodeShift\Util\CallStackInterface;
use Psr\Log\LoggerInterface;

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
    private static ?EnvironmentInterface $environment;
    private static bool $initialised = false;
    private static ?DelegatingLoggerInterface $logger;
    private static ?StatResolverInterface $statResolver;
    private static ?UnwrapperInterface $unwrapper;

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
        self::$environment = new Environment();
        self::$logger = new DelegatingLogger();
        self::$unwrapper = new Unwrapper();
        self::$statResolver = new AclStatResolver(
            new NativeStatResolver(self::$unwrapper),
            new CachingPosix(new Posix()),
            new AccessResolver(self::$unwrapper)
        );
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
     * Fetches the Environment service.
     */
    public static function getEnvironment(): EnvironmentInterface
    {
        return self::$environment;
    }

    /**
     * Fetches the delegating Logger service.
     */
    public static function getLogger(): DelegatingLoggerInterface
    {
        return self::$logger;
    }

    /**
     * Fetches the StatResolver service.
     */
    public static function getStatResolver(): StatResolverInterface
    {
        return self::$statResolver;
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
     * Fetches the Unwrapper service.
     */
    public static function getUnwrapper(): UnwrapperInterface
    {
        return self::$unwrapper;
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
     * Installs a new Environment.
     */
    public static function setEnvironment(EnvironmentInterface $environment): void
    {
        self::$environment = $environment;
    }

    /**
     * Installs a new Logger.
     */
    public static function setLogger(LoggerInterface $logger): void
    {
        self::$logger->setInnerLogger($logger);
    }

    /**
     * Installs a new StatResolver.
     */
    public static function setStatResolver(StatResolverInterface $statResolver): void
    {
        self::$statResolver = $statResolver;
    }

    /**
     * Installs a new Unwrapper.
     */
    public static function setUnwrapper(UnwrapperInterface $unwrapper): void
    {
        self::$unwrapper = $unwrapper;
    }

    /**
     * Uninitialises PHP Code Shift. Only really useful for testing.
     */
    public static function uninitialise(): void
    {
        self::$bootstrap = null;
        self::$callStack = null;
        self::$environment = null;
        self::$initialised = false;
    }
}
