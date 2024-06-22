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

use Asmblah\PhpCodeShift\Cache\CacheInterface;
use Asmblah\PhpCodeShift\Cache\Provider\PackageCacheProvider;
use InvalidArgumentException;
use Nytris\Core\Package\PackageContextInterface;
use Nytris\Core\Package\PackageInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Shift.
 *
 * Defines the public facade API for the library.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Shift implements ShiftInterface
{
    /**
     * @inheritDoc
     */
    public function getCache(): CacheInterface
    {
        return Shared::getBootstrap()->getCache();
    }

    /**
     * @inheritDoc
     */
    public function getLogger(): LoggerInterface
    {
        return Shared::getLogger()->getInnerLogger();
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'shift';
    }

    /**
     * @inheritDoc
     */
    public static function getVendor(): string
    {
        return 'nytris';
    }

    /**
     * @inheritDoc
     */
    public static function install(PackageContextInterface $packageContext, PackageInterface $package): void
    {
        if (!$package instanceof ShiftPackageInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    'Package config must be a %s but it was a %s',
                    ShiftPackageInterface::class,
                    $package::class
                )
            );
        }

        $cacheLayerFactory = $package->getCacheLayerFactory();

        Shared::initialise();

        Shared::getBootstrap()->install(new PackageCacheProvider($cacheLayerFactory, $packageContext, $package));
    }

    /**
     * @inheritDoc
     */
    public static function isInstalled(): bool
    {
        return Shared::getBootstrap()->isInstalled();
    }

    /**
     * @inheritDoc
     */
    public function setLogger(LoggerInterface $logger): void
    {
        Shared::setLogger($logger);
    }

    /**
     * @inheritDoc
     */
    public static function uninstall(): void
    {
        Shared::getBootstrap()->uninstall();
    }
}
