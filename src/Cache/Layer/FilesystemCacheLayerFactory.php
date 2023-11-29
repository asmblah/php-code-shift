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

namespace Asmblah\PhpCodeShift\Cache\Layer;

use Asmblah\PhpCodeShift\Cache\Adapter\CacheAdapterInterface;
use Asmblah\PhpCodeShift\Cache\Adapter\FilesystemCacheAdapter;
use Asmblah\PhpCodeShift\Cache\Driver\CacheDriverInterface;
use Asmblah\PhpCodeShift\Cache\Driver\FilesystemCacheDriver;
use Asmblah\PhpCodeShift\Cache\Warmer\FilesystemCacheWarmer;
use Asmblah\PhpCodeShift\Filesystem\Filesystem;
use Asmblah\PhpCodeShift\Filesystem\FilesystemInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Shifter\ShiftSetShifterInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Resolver\ShiftSetResolverInterface;
use Asmblah\PhpCodeShift\ShiftPackageInterface;
use InvalidArgumentException;
use Nytris\Core\Package\PackageContextInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

/**
 * Class FilesystemCacheLayerFactory.
 *
 * Abstracts the creation of the cache layer adapter and driver.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class FilesystemCacheLayerFactory implements CacheLayerFactoryInterface
{
    private readonly FilesystemInterface $filesystem;

    public function __construct(
        ?FilesystemInterface $filesystem = null
    ) {
        $this->filesystem = $filesystem ?? new Filesystem(new SymfonyFilesystem());
    }

    private function buildBaseCachePath(PackageContextInterface $packageContext): string
    {
        return $packageContext->getPackageCachePath() . DIRECTORY_SEPARATOR . 'php';
    }

    /**
     * @inheritDoc
     */
    public function createCacheAdapter(PackageContextInterface $packageContext): CacheAdapterInterface
    {
        $baseCachePath = $this->buildBaseCachePath($packageContext);

        return new FilesystemCacheAdapter(
            $this->filesystem,
            $packageContext->resolveProjectRoot(),
            $baseCachePath
        );
    }

    /**
     * @inheritDoc
     */
    public function createCacheDriver(
        CacheAdapterInterface $cacheAdapter,
        ShiftSetResolverInterface $shiftSetResolver,
        ShiftSetShifterInterface $shiftSetShifter,
        LoggerInterface $logger,
        PackageContextInterface $packageContext,
        ShiftPackageInterface $package
    ): CacheDriverInterface {
        if (!$cacheAdapter instanceof FilesystemCacheAdapter) {
            throw new InvalidArgumentException(
                sprintf(
                    'Cache adapter must be a %s but it was a %s',
                    FilesystemCacheAdapter::class,
                    $cacheAdapter::class
                )
            );
        }

        $baseCachePath = $this->buildBaseCachePath($packageContext);

        $warmer = new FilesystemCacheWarmer(
            $cacheAdapter,
            $this->filesystem,
            $shiftSetResolver,
            $shiftSetShifter,
            $logger
        );

        return new FilesystemCacheDriver(
            $this->filesystem,
            $warmer,
            $logger,
            $packageContext->resolveProjectRoot(),
            $package->getRelativeSourcePaths(),
            $package->getSourcePattern(),
            $baseCachePath
        );
    }
}
