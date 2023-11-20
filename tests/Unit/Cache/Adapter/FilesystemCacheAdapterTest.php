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

namespace Asmblah\PhpCodeShift\Tests\Unit\Cache\Adapter;

use Asmblah\PhpCodeShift\Cache\Adapter\FilesystemCacheAdapter;
use Asmblah\PhpCodeShift\Exception\FileNotCachedException;
use Asmblah\PhpCodeShift\Exception\NativeFileOperationFailedException;
use Asmblah\PhpCodeShift\Filesystem\FilesystemInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use InvalidArgumentException;
use Mockery\MockInterface;

/**
 * Class FilesystemCacheAdapterTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class FilesystemCacheAdapterTest extends AbstractTestCase
{
    private FilesystemCacheAdapter $adapter;
    private MockInterface&FilesystemInterface $filesystem;

    public function setUp(): void
    {
        $this->filesystem = mock(FilesystemInterface::class);

        $this->adapter = new FilesystemCacheAdapter(
            $this->filesystem,
            '/my/project/root',
            '/my/base/cache/path'
        );
    }

    public function testBuildCachePathBuildsCorrectly(): void
    {
        static::assertSame(
            '/my/base/cache/path/stuff/some_module.php',
            $this->adapter->buildCachePath('/my/project/root/stuff/some_module.php')
        );
    }

    public function testBuildCachePathRaisesExceptionWhenPathIsOutsideProjectRoot(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Path "/some/path/outside/project_root.php" must be inside project root "/my/project/root/" but is not'
        );

        $this->adapter->buildCachePath('/some/path/outside/project_root.php');
    }

    public function testHasFileReturnsTrueForCachedFile(): void
    {
        $this->filesystem->allows()
            ->fileExists('/my/base/cache/path/stuff/my_module.php')
            ->andReturnTrue();

        static::assertTrue($this->adapter->hasFile('/my/project/root/stuff/my_module.php'));
    }

    public function testHasFileReturnsFalseForNonCachedFile(): void
    {
        $this->filesystem->allows()
            ->fileExists('/my/base/cache/path/stuff/my_non_existent_module.php')
            ->andReturnFalse();

        static::assertFalse($this->adapter->hasFile('/my/project/root/stuff/my_non_existent_module.php'));
    }

    public function testOpenFileOpensCorrectPathFromCacheForReading(): void
    {
        $stream = fopen('php://memory', 'rb');
        $this->filesystem->allows()
            ->openForRead('/my/base/cache/path/stuff/my_module.php')
            ->andReturn($stream);

        static::assertSame($stream, $this->adapter->openFile('/my/project/root/stuff/my_module.php'));
    }

    public function testSaveFileWritesToCorrectFileInCache(): void
    {
        $this->filesystem->expects()
            ->writeFile('/my/base/cache/path/stuff/my_module.php', '<?php return "my result";')
            ->once();

        $this->adapter->saveFile('/my/project/root/stuff/my_module.php', '<?php return "my result";');
    }

    public function testSaveFileRaisesFileNotCachedExceptionOnNativeFileIoException(): void
    {
        $nativeFileIoException = new NativeFileOperationFailedException('Bang!');
        $this->filesystem->expects()
            ->writeFile('/my/base/cache/path/stuff/my_module.php', '<?php return "my result";')
            ->andThrow($nativeFileIoException);

        $this->expectException(FileNotCachedException::class);
        $this->expectExceptionMessage(
            'Failed to write 25 byte(s) to cache file path: "/my/base/cache/path/stuff/my_module.php"'
        );

        $this->adapter->saveFile('/my/project/root/stuff/my_module.php', '<?php return "my result";');
    }
}
