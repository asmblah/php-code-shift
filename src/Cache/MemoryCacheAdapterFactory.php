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

use Nytris\Core\Package\PackageContextInterface;

/**
 * Class MemoryCacheAdapterFactory.
 *
 * Abstracts the creation of the cache adapter.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class MemoryCacheAdapterFactory implements CacheAdapterFactoryInterface
{
    /**
     * @inheritDoc
     */
    public function createCacheAdapter(PackageContextInterface $packageContext): CacheAdapterInterface
    {
        return new MemoryCacheAdapter();
    }
}
