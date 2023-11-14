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

namespace Asmblah\PhpCodeShift;

use Asmblah\PhpCodeShift\Cache\CacheAdapterFactoryInterface;
use Asmblah\PhpCodeShift\Cache\MemoryCacheAdapterFactory;

/**
 * Class ShiftPackage.
 *
 * Configures the installation of PHP Code Shift.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ShiftPackage implements ShiftPackageInterface
{
    private readonly CacheAdapterFactoryInterface $cacheAdapterFactory;

    public function __construct(
        ?CacheAdapterFactoryInterface $cacheAdapterFactory = null
    ) {
        $this->cacheAdapterFactory = $cacheAdapterFactory ?? new MemoryCacheAdapterFactory();
    }

    /**
     * @inheritDoc
     */
    public function getCacheAdapterFactory(): CacheAdapterFactoryInterface
    {
        return $this->cacheAdapterFactory;
    }

    /**
     * @inheritDoc
     */
    public function getPackageFacadeFqcn(): string
    {
        return Shift::class;
    }
}
