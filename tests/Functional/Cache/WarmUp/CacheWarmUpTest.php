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

namespace Asmblah\PhpCodeShift\Tests\Functional\Cache\WarmUp;

use Asmblah\PhpCodeShift\Cache\Layer\FilesystemCacheLayerFactory;
use Asmblah\PhpCodeShift\CodeShift;
use Asmblah\PhpCodeShift\Shift;
use Asmblah\PhpCodeShift\Shifter\Filter\FileFilter;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\String\StringLiteralShiftSpec;
use Asmblah\PhpCodeShift\ShiftPackageInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery\MockInterface;
use Nytris\Core\Package\PackageContextInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class CacheWarmUpTest.
 *
 * Tests warming the cache with syntactically correct code.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class CacheWarmUpTest extends AbstractTestCase
{
    private string $packageCachePath;
    private MockInterface&PackageContextInterface $packageContext;
    private Shift $shift;
    private ShiftPackageInterface $shiftPackage;
    private Filesystem $symfonyFilesystem;

    public function setUp(): void
    {
        parent::setUp();

        $this->packageCachePath = dirname(__DIR__, 4) . '/var/cache/warmup/project/nytris/shift';
        $this->packageContext = mock(PackageContextInterface::class, [
            'getPackageCachePath' => $this->packageCachePath,
            'resolveProjectRoot' => dirname(__DIR__, 4),
        ]);
        $this->shift = new Shift();
        $this->shiftPackage = mock(ShiftPackageInterface::class, [
            'getCacheLayerFactory' => new FilesystemCacheLayerFactory(),
            'getRelativeSourcePaths' => [
                'tests/Functional/Fixtures/cache/warmup/project/src',
            ],
            'getSourcePattern' => ShiftPackageInterface::DEFAULT_SOURCE_PATTERN,
        ]);
        $this->symfonyFilesystem = new Filesystem();

        Shift::uninstall();
        Shift::install($this->packageContext, $this->shiftPackage);

        $codeShift = new CodeShift();
        $codeShift->shift(
            new StringLiteralShiftSpec('Hello', 'Goodbye'),
            new FileFilter('**/MyStuff.php')
        );
        $codeShift->install();

        $this->symfonyFilesystem->remove($this->packageCachePath);
        $this->symfonyFilesystem->mkdir($this->packageCachePath);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        Shift::uninstall();

        $this->symfonyFilesystem->remove($this->packageCachePath);
    }

    public function testCacheWarmUpWarmsCacheCorrectly(): void
    {
        $cacheFilePath = $this->packageCachePath . '/php/tests/Functional/Fixtures/cache/warmup/project/src/My/Stuff/MyStuff.php';
        $expectedContents = <<<PHP
<?php

declare(strict_types=1);

namespace Asmblah\PhpCodeShift\Tests\Functional\Fixtures\cache\warmup\project\src\My\Stuff;

class MyStuff
{
    public function getGreeting(): string
    {
        return 'Goodbye!';
    }
}

PHP;

        $this->shift->getCache()->warmUp();

        static::assertTrue(is_file($cacheFilePath));
        static::assertSame($expectedContents, file_get_contents($cacheFilePath));
    }
}
