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

/**
 * Interface FilesystemCacheAdapterInterface.
 *
 * Manages the storage of shifted code on disk.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface FilesystemCacheAdapterInterface extends CacheAdapterInterface
{
    /**
     * Builds the path to the corresponding file in the cache for the given original file.
     */
    public function buildCachePath(string $originalPath): string;
}
