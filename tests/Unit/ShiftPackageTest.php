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

namespace Asmblah\PhpCodeShift\Tests\Unit;

use Asmblah\PhpCodeShift\Cache\Layer\CacheLayerFactoryInterface;
use Asmblah\PhpCodeShift\Cache\Layer\MemoryCacheLayerFactory;
use Asmblah\PhpCodeShift\Shift;
use Asmblah\PhpCodeShift\ShiftPackage;
use Asmblah\PhpCodeShift\ShiftPackageInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;

/**
 * Class ShiftPackageTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ShiftPackageTest extends AbstractTestCase
{
    public function testGetCacheLayerFactoryReturnsTheGivenFactory(): void
    {
        $factory = mock(CacheLayerFactoryInterface::class);
        $package = new ShiftPackage(cacheLayerFactory: $factory);

        static::assertSame($factory, $package->getCacheLayerFactory());
    }

    public function testAMemoryCacheLayerFactoryIsUsedByDefault(): void
    {
        $package = new ShiftPackage();

        static::assertInstanceOf(MemoryCacheLayerFactory::class, $package->getCacheLayerFactory());
    }

    public function testGetPackageFacadeFqcnReturnsTheCorrectFqcn(): void
    {
        $package = new ShiftPackage();

        static::assertSame(Shift::class, $package->getPackageFacadeFqcn());
    }

    public function testGetRelativeSourcePathsReturnsTheGivenPaths(): void
    {
        $package = new ShiftPackage(relativeSourcePaths: ['first/path/here', 'second/path/there']);

        static::assertEquals(['first/path/here', 'second/path/there'], $package->getRelativeSourcePaths());
    }

    public function testGetRelativeSourcePathsReturnsTheCorrectPathsByDefault(): void
    {
        $package = new ShiftPackage();

        static::assertEquals(['src', 'tests'], $package->getRelativeSourcePaths());
    }

    public function testGetSourcePatternReturnsTheGivenPattern(): void
    {
        $package = new ShiftPackage(sourcePattern: '.+\.(inc|php)');

        static::assertSame('.+\.(inc|php)', $package->getSourcePattern());
    }

    public function testGetSourcePatternReturnsTheCorrectPatternByDefault(): void
    {
        $package = new ShiftPackage();

        static::assertSame(ShiftPackageInterface::DEFAULT_SOURCE_PATTERN, $package->getSourcePattern());
    }

    public function testValidateTimestampsReturnsFalseByDefault(): void
    {
        $package = new ShiftPackage();

        static::assertFalse($package->validateTimestamps());
    }

    public function testValidateTimestampsReturnsTrueWhenSpecified(): void
    {
        $package = new ShiftPackage(validateTimestamps: true);

        static::assertTrue($package->validateTimestamps());
    }

    public function testValidateTimestampsReturnsFalseWhenSpecifiedExplicitly(): void
    {
        $package = new ShiftPackage(validateTimestamps: false);

        static::assertFalse($package->validateTimestamps());
    }
}
