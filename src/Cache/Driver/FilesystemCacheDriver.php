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

use Asmblah\PhpCodeShift\Cache\Warmer\WarmerInterface;
use Asmblah\PhpCodeShift\Exception\DirectoryNotFoundException;
use Asmblah\PhpCodeShift\Filesystem\FilesystemInterface;
use Psr\Log\LoggerInterface;

/**
 * Class FilesystemCacheDriver.
 *
 * Manages the persistent cache when stored on disk.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class FilesystemCacheDriver implements CacheDriverInterface
{
    private readonly string $projectRootPath;

    /**
     * @param FilesystemInterface $filesystem
     * @param WarmerInterface $warmer
     * @param LoggerInterface $logger
     * @param string $projectRootPath
     * @param string[] $relativeSourcePaths
     * @param string $sourcePattern
     * @param string $baseCachePath
     */
    public function __construct(
        private readonly FilesystemInterface $filesystem,
        private readonly WarmerInterface $warmer,
        private readonly LoggerInterface $logger,
        string $projectRootPath,
        private readonly array $relativeSourcePaths,
        private readonly string $sourcePattern,
        private readonly string $baseCachePath
    ) {
        $this->projectRootPath = rtrim($projectRootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * @inheritDoc
     */
    public function clear(): void
    {
        $this->logger->info('Clearing Nytris Shift cache...');

        $this->filesystem->remove($this->baseCachePath);

        $this->logger->info('Nytris Shift cache cleared');
    }

    /**
     * @inheritDoc
     */
    public function warmUp(): void
    {
        $this->logger->info('Warming Nytris Shift cache...');

        $this->filesystem->mkdir($this->baseCachePath);

        foreach ($this->relativeSourcePaths as $relativeSourcePath) {
            $this->logger->info('Entering directory for Nytris Shift cache warm...', [
                'directory' => $relativeSourcePath,
            ]);

            $directoryPath = $this->projectRootPath . $relativeSourcePath;

            if (!$this->filesystem->directoryExists($directoryPath)) {
                throw new DirectoryNotFoundException(
                    sprintf(
                        'Cannot warm relative source path "%s": path "%s" does not exist',
                        $relativeSourcePath,
                        $directoryPath
                    )
                );
            }

            $regexIterator = $this->filesystem->iterateDirectory($directoryPath, $this->sourcePattern);

            foreach ($regexIterator as $regexMatches) {
                $this->warmer->warmFile($regexMatches[0]);
            }
        }

        $this->logger->info('Nytris Shift cache warmed');
    }
}
