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

namespace Asmblah\PhpCodeShift\Cache\Adapter;

use Asmblah\PhpCodeShift\Exception\FileNotCachedException;

/**
 * Interface CacheAdapterInterface.
 *
 * Manages the storage of shifted code.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface CacheAdapterInterface
{
    /**
     * Determines whether the given original file path is in the cache and not stale.
     */
    public function hasFile(string $path): bool;

    /**
     * Opens the cache file for the given original file path as a readable stream.
     *
     * @param string $path Original file path.
     * @return resource
     * @throws FileNotCachedException When unable to read the cache file.
     */
    public function openFile(string $path);

    /**
     * Caches the given shifted code contents against the provided original file path.
     *
     * @throws FileNotCachedException When unable to write the cache file.
     */
    public function saveFile(string $path, string $shiftedContents): void;
}
