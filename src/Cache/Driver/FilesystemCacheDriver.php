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
use Asmblah\PhpCodeShift\Exception\DirectoryNotFoundException;
use Asmblah\PhpCodeShift\Exception\FileNotCachedException;
use Asmblah\PhpCodeShift\Filesystem\FilesystemInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Shifter\ShiftSetShifterInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Resolver\ShiftSetResolverInterface;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

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
            $directoryPath = $this->projectRootPath . DIRECTORY_SEPARATOR . $relativeSourcePath;

            if (!is_dir($directoryPath)) {
                throw new DirectoryNotFoundException(
                    sprintf(
                        'Cannot warm relative source path "%s": path "%s" does not exist',
                        $relativeSourcePath,
                        $directoryPath
                    )
                );
            }

            $directoryIterator = new RecursiveDirectoryIterator(
                $directoryPath,
                FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_PATHNAME
            );
            $regexIterator = new RegexIterator(
                new RecursiveIteratorIterator($directoryIterator),
                '/^.+\.php$/i',
                RegexIterator::GET_MATCH
            );

            foreach ($regexIterator as $regexMatches) {
                $this->warmFile($regexMatches[0]);
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
