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

/**
 * Class NullCacheDriver.
 *
 * Manages the persistent cache. Represents a non-existent persistent cache,
 * meaning that shifts must be re-applied every program run/web request etc.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class NullCacheDriver implements CacheDriverInterface
{
    /**
     * @inheritDoc
     */
    public function clear(): void
    {
        // Nothing to do.
    }

    /**
     * @inheritDoc
     */
    public function warmUp(): void
    {
        // Nothing to do.
    }
}
