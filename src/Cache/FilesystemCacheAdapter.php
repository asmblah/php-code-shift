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

namespace Asmblah\PhpCodeShift\Cache;

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
class FilesystemCacheAdapter implements CacheAdapterInterface
{
    public function __construct(
        private readonly FilesystemInterface $filesystem,
        private readonly string $basePath
    ) {
    }

    /**
     * Builds the path to the corresponding file in the cache for the given original file.
     */
    private function buildCachePath(string $originalPath): string
    {
        return $this->basePath . $originalPath;
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
        try {
            $this->filesystem->writeFile($path, $shiftedContents);
        } catch (NativeFileOperationFailedException $exception) {
            throw new FileNotCachedException(
                sprintf(
                    'Failed to write %d byte(s) to cache file path: "%s"',
                    strlen($shiftedContents),
                    $path
                ),
                0,
                $exception
            );
        }
    }
}
