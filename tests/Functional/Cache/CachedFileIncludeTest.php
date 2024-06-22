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

namespace Asmblah\PhpCodeShift\Tests\Functional\Cache;

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
 * Class CachedFileIncludeTest.
 *
 * Tests caching of files both inside and outside the project root.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class CachedFileIncludeTest extends AbstractTestCase
{
    private TestLogger $logger;
    private string $packageCachePath;
    private MockInterface&PackageContextInterface $packageContext;
    private string $projectRoot;
    private string $relativeProjectRoot;
    private Shift $shift;
    private ShiftPackageInterface $shiftPackage;
    private Filesystem $symfonyFilesystem;

    public function setUp(): void
    {
        parent::setUp();

        $projectRoot = dirname(__DIR__, 3);
        $this->relativeProjectRoot = 'tests/Functional/Fixtures/cache/include/project/src';

        $this->logger = new TestLogger();
        $this->projectRoot = $projectRoot . '/' . $this->relativeProjectRoot;
        $this->packageCachePath = $projectRoot . '/var/cache/include/project/nytris/shift';
        $this->packageContext = mock(PackageContextInterface::class, [
            'getPackageCachePath' => $this->packageCachePath,
            'resolveProjectRoot' => $projectRoot,
        ]);
        $this->shift = new Shift();
        $this->shiftPackage = mock(ShiftPackageInterface::class, [
            'getCacheLayerFactory' => new FilesystemCacheLayerFactory(),
            'getRelativeSourcePaths' => [
                $this->relativeProjectRoot,
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
            new FileFilter('**/my_script.php')
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

    public function testFilesOutsideProjectRootAreCachedAndHandledCorrectly(): void
    {
        $path = realpath(sys_get_temp_dir()) . '/my_script.php';
        file_put_contents($path, '<?php return "Hello!";');

        static::assertSame('Goodbye!', require $path);
        static::assertTrue(is_file($this->packageCachePath . '/php/fsroot' . $path));
    }

    public function testFilesInsideProjectRootAreCachedAndHandledCorrectly(): void
    {
        $path = $this->projectRoot . '/my_script.php';

        static::assertSame('Goodbye!', require $path);
        static::assertTrue(
            is_file($this->packageCachePath . '/php/project/' . $this->relativeProjectRoot . '/my_script.php')
        );
    }
}
