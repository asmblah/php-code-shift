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

namespace Asmblah\PhpCodeShift\Cache\Adapter;

use Asmblah\PhpCodeShift\Exception\FileNotCachedException;
use Asmblah\PhpCodeShift\Exception\NativeFileOperationFailedException;
use Asmblah\PhpCodeShift\Filesystem\FilesystemInterface;

/**
 * Class FilesystemCacheAdapter.
 *
 * Manages the storage of shifted code on disk.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class FilesystemCacheAdapter implements FilesystemCacheAdapterInterface
{
    private readonly string $projectRootPath;
    private readonly int $projectRootPathLength;

    public function __construct(
        private readonly FilesystemInterface $filesystem,
        string $projectRootPath,
        private readonly string $baseCachePath
    ) {
        $this->projectRootPath = rtrim($projectRootPath, '/') . '/';

        // Cache length to avoid having to calculate on every lookup.
        $this->projectRootPathLength = strlen($this->projectRootPath);
    }

    /**
     * @inheritDoc
     */
    public function buildCachePath(string $originalPath): string
    {
        if (str_starts_with($originalPath, $this->projectRootPath)) {
            // Strip the project path prefix from files in the cache.
            $cacheRelativeFilePath = 'project/' . substr($originalPath, $this->projectRootPathLength);
        } else {
            // Otherwise for paths outside the project root, use the separate /fsroot/ namespace.
            $cacheRelativeFilePath = 'fsroot/' . ltrim($originalPath, '/');
        }

        return $this->baseCachePath . DIRECTORY_SEPARATOR . $cacheRelativeFilePath;
    }

    /**
     * @inheritDoc
     */
    public function hasFile(string $path): bool
    {
        $cachePath = $this->buildCachePath($path);

        return $this->filesystem->fileExists($cachePath);
    }

    /**
     * @inheritDoc
     */
    public function openFile(string $path)
    {
        $cachePath = $this->buildCachePath($path);

        return $this->filesystem->openForRead($cachePath);
    }

    /**
     * @inheritDoc
     */
    public function saveFile(string $path, string $shiftedContents): void
    {
        $cachePath = $this->buildCachePath($path);

        try {
            $this->filesystem->writeFile($cachePath, $shiftedContents);
        } catch (NativeFileOperationFailedException $exception) {
            throw new FileNotCachedException(
                sprintf(
                    'Failed to write %d byte(s) to cache file path: "%s"',
                    strlen($shiftedContents),
                    $cachePath
                ),
                0,
                $exception
            );
        }
    }
}
