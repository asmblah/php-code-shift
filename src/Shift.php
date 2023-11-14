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

use InvalidArgumentException;
use Nytris\Core\Package\PackageContextInterface;
use Nytris\Core\Package\PackageInterface;

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

        $cacheAdapter = $package->getCacheAdapterFactory()->createCacheAdapter($packageContext);

        Shared::getBootstrap()->install($cacheAdapter);
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
    public static function uninstall(): void
    {
        Shared::getBootstrap()->uninstall();
    }
}
