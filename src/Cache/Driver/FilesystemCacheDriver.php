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

namespace Asmblah\PhpCodeShift\Cache\Driver;

use Asmblah\PhpCodeShift\Cache\Adapter\FilesystemCacheAdapterInterface;
use Asmblah\PhpCodeShift\Exception\FileNotCachedException;
use Asmblah\PhpCodeShift\Filesystem\FilesystemInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Shifter\ShiftSetShifterInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Resolver\ShiftSetResolverInterface;
use DirectoryIterator;

/**
 * Class FilesystemCacheDriver.
 *
 * Manages the persistent cache when stored on disk.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class FilesystemCacheDriver implements CacheDriverInterface
{
    /**
     * @param FilesystemCacheAdapterInterface $cacheAdapter
     * @param FilesystemInterface $filesystem
     * @param string $projectRootPath
     * @param string[] $relativeSourcePaths
     * @param string $baseCachePath
     */
    public function __construct(
        private readonly FilesystemCacheAdapterInterface $cacheAdapter,
        private readonly FilesystemInterface $filesystem,
        private readonly ShiftSetResolverInterface $shiftSetResolver,
        private readonly ShiftSetShifterInterface $shiftSetShifter,
        private readonly string $projectRootPath,
        private readonly array $relativeSourcePaths,
        private readonly string $baseCachePath
    ) {
    }

    /**
     * @inheritDoc
     */
    public function clear(): void
    {
        $this->filesystem->remove($this->baseCachePath);
    }

    /**
     * @inheritDoc
     */
    public function warmUp(): void
    {
        $this->filesystem->mkdir($this->baseCachePath);

        foreach ($this->relativeSourcePaths as $relativeSourcePath) {
            $directoryIterator = new DirectoryIterator(
                $this->projectRootPath . DIRECTORY_SEPARATOR . $relativeSourcePath
            );

            $this->warmDirectory($directoryIterator);
        }
    }

    /**
     * Traverses the given directory, warming any applicable PHP files into the cache.
     *
     * @throws FileNotCachedException
     */
    private function warmDirectory(DirectoryIterator $directoryIterator): void
    {
        /** @var DirectoryIterator $fileInfo */
        foreach ($directoryIterator as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if ($fileInfo->isDir()) {
                $this->warmDirectory($fileInfo);
                continue;
            }

            if ($fileInfo->isFile() && strtolower($fileInfo->getExtension()) === 'php') {
                $this->warmFile($fileInfo->getPathname());
            }
        }
    }

    /**
     * Warms the given file, pre-shifting it into the cache.
     *
     * @throws FileNotCachedException
     */
    private function warmFile(string $filePath): void
    {
        $shiftSet = $this->shiftSetResolver->resolveShiftSet($filePath);

        if ($shiftSet === null) {
            // No shifts apply to this file - nothing to do.
            return;
        }

        $originalContents = $this->filesystem->readFile($filePath);
        $shiftedContents = $this->shiftSetShifter->shift($originalContents, $shiftSet);

        $this->cacheAdapter->saveFile($filePath, $shiftedContents);
    }
}
