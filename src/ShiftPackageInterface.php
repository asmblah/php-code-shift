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
use Nytris\Core\Package\PackageInterface;

/**
 * Interface ShiftPackageInterface.
 *
 * Configures the installation of PHP Code Shift.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface ShiftPackageInterface extends PackageInterface
{
    /**
     * Fetches the cache adapter factory to use.
     */
    public function getCacheAdapterFactory(): CacheAdapterFactoryInterface;
}
