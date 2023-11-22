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

use Asmblah\PhpCodeShift\Cache\Driver\CacheDriverInterface;

/**
 * Class Cache.
 *
 * Manages the cache.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Cache implements CacheInterface
{
    public function __construct(
        private readonly CacheDriverInterface $cacheDriver
    ) {
    }

    /**
     * @inheritDoc
     */
    public function clear(): void
    {
        $this->cacheDriver->clear();
    }

    /**
     * @inheritDoc
     */
    public function warmUp(): void
    {
        $this->cacheDriver->warmUp();
    }
}
