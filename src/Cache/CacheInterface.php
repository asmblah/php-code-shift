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

/**
 * Interface CacheInterface.
 *
 * Manages the cache.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface CacheInterface
{
    /**
     * Clears the entire cache.
     */
    public function clear(): void;

    /**
     * Warms the entire cache.
     *
     * @throws FileNotCachedException When there is an issue writing a file to the persistent cache.
     */
    public function warmUp(): void;
}
