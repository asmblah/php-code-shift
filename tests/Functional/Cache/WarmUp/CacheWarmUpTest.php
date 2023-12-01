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
use Asmblah\PhpCodeShift\Tests\Functional\Util\TestLogger;
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
    private TestLogger $logger;
    private string $packageCachePath;
    private MockInterface&PackageContextInterface $packageContext;
    private string $projectRoot;
    private Shift $shift;
    private ShiftPackageInterface $shiftPackage;
    private Filesystem $symfonyFilesystem;

    public function setUp(): void
    {
        parent::setUp();

        $projectRoot = dirname(__DIR__, 4);
        $relativeProjectRoot = 'tests/Functional/Fixtures/cache/warmup/project/src';

        $this->logger = new TestLogger();
        $this->projectRoot = $projectRoot . '/' . $relativeProjectRoot;
        $this->packageCachePath = $projectRoot . '/var/cache/warmup/project/nytris/shift';
        $this->packageContext = mock(PackageContextInterface::class, [
            'getPackageCachePath' => $this->packageCachePath,
            'resolveProjectRoot' => $projectRoot,
        ]);
        $this->shift = new Shift();
        $this->shiftPackage = mock(ShiftPackageInterface::class, [
            'getCacheLayerFactory' => new FilesystemCacheLayerFactory(),
            'getRelativeSourcePaths' => [
                $relativeProjectRoot,
            ],
            'getSourcePattern' => ShiftPackageInterface::DEFAULT_SOURCE_PATTERN,
        ]);
        $this->symfonyFilesystem = new Filesystem();

        $this->shift->setLogger($this->logger);

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

        static::assertEquals(
            [
                [
                    'info',
                    'Warming Nytris Shift cache...',
                    [],
                ],
                [
                    'info',
                    'Entering directory for Nytris Shift cache warm...',
                    [
                        'directory' => 'tests/Functional/Fixtures/cache/warmup/project/src',
                    ],
                ],
                [
                    'info',
                    'Nytris Shift successfully warmed cache file',
                    [
                        'path' => $this->projectRoot . '/My/Stuff/MyStuff.php',
                    ],
                ],
                [
                    'info',
                    'Nytris Shift cache warmed',
                    [],
                ],
            ],
            $this->logger->getLogs()
        );
        static::assertTrue(is_file($cacheFilePath));
        static::assertSame($expectedContents, file_get_contents($cacheFilePath));
    }

    public function testCacheWarmUpAllowsLoggerToBeSetLater(): void
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
        $laterLogger = new TestLogger();
        $this->shift->setLogger($laterLogger);

        $this->shift->getCache()->warmUp();

        static::assertEquals(
            [
                [
                    'info',
                    'Warming Nytris Shift cache...',
                    [],
                ],
                [
                    'info',
                    'Entering directory for Nytris Shift cache warm...',
                    [
                        'directory' => 'tests/Functional/Fixtures/cache/warmup/project/src',
                    ],
                ],
                [
                    'info',
                    'Nytris Shift successfully warmed cache file',
                    [
                        'path' => $this->projectRoot . '/My/Stuff/MyStuff.php',
                    ],
                ],
                [
                    'info',
                    'Nytris Shift cache warmed',
                    [],
                ],
            ],
            $laterLogger->getLogs()
        );
        static::assertTrue(is_file($cacheFilePath));
        static::assertSame($expectedContents, file_get_contents($cacheFilePath));
    }
}
