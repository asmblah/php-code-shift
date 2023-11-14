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
 * Interface CacheAdapterFactoryInterface.
 *
 * Abstracts the creation of the cache adapter.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface CacheAdapterFactoryInterface
{
    /**
     * Creates the cache adapter to use.
     */
    public function createCacheAdapter(PackageContextInterface $packageContext): CacheAdapterInterface;
}
