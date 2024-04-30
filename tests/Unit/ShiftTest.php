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

namespace Asmblah\PhpCodeShift\Tests\Unit;

use Asmblah\PhpCodeShift\Cache\Adapter\CacheAdapterInterface;
use Asmblah\PhpCodeShift\Cache\CacheInterface;
use Asmblah\PhpCodeShift\Cache\Driver\CacheDriverInterface;
use Asmblah\PhpCodeShift\Cache\Layer\CacheLayerFactoryInterface;
use Asmblah\PhpCodeShift\Shared;
use Asmblah\PhpCodeShift\Shift;
use Asmblah\PhpCodeShift\Shifter\Stream\StreamWrapperManager;
use Asmblah\PhpCodeShift\ShiftPackageInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use InvalidArgumentException;
use Nytris\Core\Package\PackageContextInterface;
use Nytris\Core\Package\PackageInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ShiftTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ShiftTest extends AbstractTestCase
{
    private Shift $shift;

    public function setUp(): void
    {
        Shared::uninitialise();
        StreamWrapperManager::uninitialise();

        $this->shift = new Shift();
    }

    public function tearDown(): void
    {
        Shared::uninitialise();
        StreamWrapperManager::uninitialise();
        Shared::initialise();
        StreamWrapperManager::initialise();
    }

    public function testGetCacheFetchesTheCache(): void
    {
        Shared::initialise();
        StreamWrapperManager::initialise();

        static::assertInstanceOf(CacheInterface::class, $this->shift->getCache());
    }

    public function testGetLoggerFetchesTheLogger(): void
    {
        static::assertInstanceOf(LoggerInterface::class, $this->shift->getLogger());
    }

    public function testGetNameReturnsCorrectName(): void
    {
        static::assertSame('shift', Shift::getName());
    }

    public function testGetNameReturnsCorrectVendor(): void
    {
        static::assertSame('nytris', Shift::getVendor());
    }

    public function testInstallCorrectlyInstallsLibrary(): void
    {
        $package = mock(ShiftPackageInterface::class, [
            'getCacheLayerFactory' => mock(CacheLayerFactoryInterface::class, [
                'createCacheAdapter' => mock(CacheAdapterInterface::class),
                'createCacheDriver' => mock(CacheDriverInterface::class),
            ]),
        ]);
        $packageContext = mock(PackageContextInterface::class);

        Shift::install($packageContext, $package);

        static::assertTrue(Shared::getBootstrap()->isInstalled());
        static::assertTrue(StreamWrapperManager::isInitialised());
    }

    public function testInstallRaisesExceptionWhenWrongPackageTypeGiven(): void
    {
        $package = mock(PackageInterface::class);
        $packageContext = mock(PackageContextInterface::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Package config must be a %s but it was a %s',
                ShiftPackageInterface::class,
                $package::class
            )
        );

        Shift::install($packageContext, $package);
    }

    public function testSetLoggerOverridesTheLogger(): void
    {
        $logger = mock(LoggerInterface::class);

        $this->shift->setLogger($logger);

        static::assertSame($logger, $this->shift->getLogger());
    }
}
