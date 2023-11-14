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

use Asmblah\PhpCodeShift\Filesystem\Filesystem;
use Asmblah\PhpCodeShift\Filesystem\FilesystemInterface;
use Nytris\Core\Package\PackageContextInterface;

/**
 * Class FilesystemCacheAdapterFactory.
 *
 * Abstracts the creation of the cache adapter.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class FilesystemCacheAdapterFactory implements CacheAdapterFactoryInterface
{
    private readonly FilesystemInterface $filesystem;

    public function __construct(
        ?FilesystemInterface $filesystem = null
    ) {
        $this->filesystem = $filesystem ?? new Filesystem();
    }

    /**
     * @inheritDoc
     */
    public function createCacheAdapter(PackageContextInterface $packageContext): CacheAdapterInterface
    {
        $baseCachePath = $packageContext->getPackageCachePath() . DIRECTORY_SEPARATOR . 'php';

        return new FilesystemCacheAdapter($this->filesystem, $baseCachePath);
    }
}
