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

namespace Asmblah\PhpCodeShift;

use Asmblah\PhpCodeShift\Cache\Layer\CacheLayerFactoryInterface;
use Asmblah\PhpCodeShift\Cache\Layer\MemoryCacheLayerFactory;

/**
 * Class ShiftPackage.
 *
 * Configures the installation of PHP Code Shift.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ShiftPackage implements ShiftPackageInterface
{
    private readonly CacheLayerFactoryInterface $cacheLayerFactory;

    /**
     * @param CacheLayerFactoryInterface|null $cacheLayerFactory
     * @param string[] $relativeSourcePaths
     * @param string $sourcePattern
     * @param bool $validateTimestamps
     */
    public function __construct(
        ?CacheLayerFactoryInterface $cacheLayerFactory = null,
        private readonly bool $validateTimestamps = false,
        private readonly array $relativeSourcePaths = ['src', 'tests'],
        private readonly string $sourcePattern = self::DEFAULT_SOURCE_PATTERN
    ) {
        $this->cacheLayerFactory = $cacheLayerFactory ?? new MemoryCacheLayerFactory();
    }

    /**
     * @inheritDoc
     */
    public function getCacheLayerFactory(): CacheLayerFactoryInterface
    {
        return $this->cacheLayerFactory;
    }

    /**
     * @inheritDoc
     */
    public function getPackageFacadeFqcn(): string
    {
        return Shift::class;
    }

    /**
     * @inheritDoc
     */
    public function getRelativeSourcePaths(): array
    {
        return $this->relativeSourcePaths;
    }

    /**
     * @inheritDoc
     */
    public function getSourcePattern(): string
    {
        return $this->sourcePattern;
    }

    /**
     * @inheritDoc
     */
    public function validateTimestamps(): bool
    {
        return $this->validateTimestamps;
    }
}
