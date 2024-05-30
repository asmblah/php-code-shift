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

namespace Asmblah\PhpCodeShift\Tests\Unit\Bootstrap;

use Asmblah\PhpCodeShift\Bootstrap\Bootstrap;
use Asmblah\PhpCodeShift\Cache\Adapter\CacheAdapterInterface;
use Asmblah\PhpCodeShift\Cache\CacheInterface;
use Asmblah\PhpCodeShift\Cache\Driver\CacheDriverInterface;
use Asmblah\PhpCodeShift\Cache\Provider\CacheProviderInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Shifter\StreamShifterInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\StreamWrapperManager;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use LogicException;
use Mockery\MockInterface;

/**
 * Class BootstrapTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class BootstrapTest extends AbstractTestCase
{
    private Bootstrap $bootstrap;
    private MockInterface&CacheAdapterInterface $cacheAdapter;
    private MockInterface&CacheDriverInterface $cacheDriver;
    private MockInterface&CacheProviderInterface $cacheProvider;

    public function setUp(): void
    {
        $this->cacheAdapter = mock(CacheAdapterInterface::class);
        $this->cacheDriver = mock(CacheDriverInterface::class);
        $this->cacheProvider = mock(CacheProviderInterface::class, [
            'createCacheAdapter' => $this->cacheAdapter,
            'createCacheDriver' => $this->cacheDriver,
        ]);

        $this->bootstrap = new Bootstrap();
    }

    public function tearDown(): void
    {
        if ($this->bootstrap->isInstalled()) {
            $this->bootstrap->uninstall();
        }
    }

    public function testGetCacheReturnsTheCache(): void
    {
        $this->bootstrap->install($this->cacheProvider);

        static::assertInstanceOf(CacheInterface::class, $this->bootstrap->getCache());
    }

    public function testGetCacheRaisesExceptionWhenNotInstalled(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot fetch Cache - not installed');

        $this->bootstrap->getCache();
    }

    public function testGetStreamShifterReturnsTheStreamShifter(): void
    {
        $this->bootstrap->install($this->cacheProvider);

        static::assertInstanceOf(StreamShifterInterface::class, $this->bootstrap->getStreamShifter());
    }

    public function testGetStreamShifterRaisesExceptionWhenNotInstalled(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot fetch StreamShifter - not installed');

        $this->bootstrap->getStreamShifter();
    }

    public function testInstallInitialisesStreamWrapperManager(): void
    {
        $this->bootstrap->install($this->cacheProvider);

        static::assertTrue(StreamWrapperManager::isInitialised());
    }

    public function testInstallReinitialisesStreamWrapperManagerIfNeeded(): void
    {
        StreamWrapperManager::initialise();
        $previousStreamHandler = StreamWrapperManager::getStreamHandler();

        $this->bootstrap->install($this->cacheProvider);

        static::assertNotSame($previousStreamHandler, StreamWrapperManager::getStreamHandler());
    }

    public function testInstallRaisesExceptionWhenAlreadyInstalled(): void
    {
        $this->bootstrap->install($this->cacheProvider);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('PHP Code Shift already installed');

        $this->bootstrap->install($this->cacheProvider);
    }

    public function testIsInstalledReturnsTrueWhenInstalled(): void
    {
        $this->bootstrap->install($this->cacheProvider);

        static::assertTrue($this->bootstrap->isInstalled());
    }

    public function testIsInstalledReturnsFalseWhenNotYetInstalled(): void
    {
        static::assertFalse($this->bootstrap->isInstalled());
    }

    public function testIsInstalledReturnsFalseWhenInstalledThenUninstalled(): void
    {
        $this->bootstrap->install($this->cacheProvider);
        $this->bootstrap->uninstall();

        static::assertFalse($this->bootstrap->isInstalled());
    }
}
