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

namespace Asmblah\PhpCodeShift\Tests\Unit\Filesystem;

use Asmblah\PhpCodeShift\Exception\NativeFileOperationFailedException;
use Asmblah\PhpCodeShift\Filesystem\Filesystem;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery\MockInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

/**
 * Class FilesystemTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class FilesystemTest extends AbstractTestCase
{
    private Filesystem $filesystem;
    private MockInterface $stubMock;
    private MockInterface&SymfonyFilesystem $symfonyFilesystem;

    public function setUp(): void
    {
        $this->stubMock = mock(['filePutContents' => false]);
        $this->symfonyFilesystem = mock(SymfonyFilesystem::class);

        $this->filesystem = new Filesystem($this->symfonyFilesystem, $this->stubMock->filePutContents(...));
    }

    public function testWriteFileCreatesTheFileWithinItsDirectory(): void
    {
        $this->symfonyFilesystem->expects()
            ->mkdir('/my/path/to')
            ->once()
            ->globally()
            ->ordered();
        $this->stubMock->expects()
            ->filePutContents('/my/path/to/my_file.txt', 'my contents')
            ->once()
            ->globally()
            ->ordered()
            ->andReturn(11);

        $this->filesystem->writeFile('/my/path/to/my_file.txt', 'my contents');
    }

    public function testWriteFileRaisesExceptionOnDirectoryCreationFailure(): void
    {
        $this->symfonyFilesystem->allows()
            ->mkdir('/my/path/to')
            ->andThrow(new IOException('Bang!'));

        $this->expectException(NativeFileOperationFailedException::class);
        $this->expectExceptionMessage('Failed to write 11 byte(s) to file path: "/my/path/to/my_file.txt"');

        $this->filesystem->writeFile('/my/path/to/my_file.txt', 'my contents');
    }

    public function testWriteFileRaisesExceptionOnWriteFailure(): void
    {
        $this->symfonyFilesystem->allows()
            ->mkdir('/my/path/to');
        $this->stubMock->allows()
            ->filePutContents('/my/path/to/my_file.txt', 'my contents')
            ->andReturnFalse();

        $this->expectException(NativeFileOperationFailedException::class);
        $this->expectExceptionMessage('Failed to write 11 byte(s) to file path: "/my/path/to/my_file.txt"');

        $this->filesystem->writeFile('/my/path/to/my_file.txt', 'my contents');
    }
}
