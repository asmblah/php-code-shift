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

namespace Asmblah\PhpCodeShift\Tests\Unit\Cache\Driver;

use ArrayIterator;
use Asmblah\PhpCodeShift\Cache\Driver\FilesystemCacheDriver;
use Asmblah\PhpCodeShift\Cache\Warmer\WarmerInterface;
use Asmblah\PhpCodeShift\Exception\DirectoryNotFoundException;
use Asmblah\PhpCodeShift\Filesystem\FilesystemInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;
use RegexIterator;

/**
 * Class FilesystemCacheDriverTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class FilesystemCacheDriverTest extends AbstractTestCase
{
    private FilesystemCacheDriver $cacheDriver;
    private MockInterface&FilesystemInterface $filesystem;
    private MockInterface&LoggerInterface $logger;
    private MockInterface&WarmerInterface $warmer;

    public function setUp(): void
    {
        $this->filesystem = mock(FilesystemInterface::class, [
            'directoryExists' => false,
            'mkdir' => null,
        ]);
        $this->logger = mock(LoggerInterface::class, [
            'info' => null,
        ]);
        $this->warmer = mock(WarmerInterface::class, [
            'warmFile' => null,
        ]);

        $this->filesystem->allows()
            ->directoryExists('/my/project/root/src')
            ->andReturnTrue()
            ->byDefault();
        $this->filesystem->allows()
            ->directoryExists('/my/project/root/stuff')
            ->andReturnTrue()
            ->byDefault();
        $this->filesystem->allows()
            ->iterateDirectory('/my/project/root/src', '#.*\.php$#')
            ->andReturn(
                new RegexIterator(
                    new ArrayIterator([
                        '/my/project/root/src/My/Stuff/MyStuff.php',
                        '/my/project/root/src/Your/Gubbins/YourGubbins.php',
                    ]),
                    '#.*\.php$#',
                    RegexIterator::GET_MATCH
                )
            )
            ->byDefault();
        $this->filesystem->allows()
            ->iterateDirectory('/my/project/root/stuff', '#.*\.php$#')
            ->andReturn(
                new RegexIterator(
                    new ArrayIterator([
                        '/my/project/root/stuff/OtherStuff.php',
                    ]),
                    '#.*\.php$#',
                    RegexIterator::GET_MATCH
                )
            )
            ->byDefault();

        $this->filesystem->allows('readFile')
            ->andReturnUsing(fn (string $path) => "The contents of '$path' are this.")
            ->byDefault();

        $this->cacheDriver = new FilesystemCacheDriver(
            $this->filesystem,
            $this->warmer,
            $this->logger,
            '/my/project/root/',
            ['src', 'stuff'],
            '#.*\.php$#',
            '/var/gubbins/cache/'
        );
    }

    public function testClearIsHandledCorrectly(): void
    {
        $this->logger->expects()
            ->info('Clearing Nytris Shift cache...')
            ->once()
            ->globally()->ordered();
        $this->filesystem->expects()
            ->remove('/var/gubbins/cache/')
            ->once()
            ->globally()->ordered();
        $this->logger->expects()
            ->info('Nytris Shift cache cleared')
            ->once()
            ->globally()->ordered();

        $this->cacheDriver->clear();
    }

    public function testWarmUpCreatesBaseCacheDirectory(): void
    {
        $this->logger->expects()
            ->info('Warming Nytris Shift cache...')
            ->once()
            ->globally()->ordered();
        $this->filesystem->expects()
            ->mkdir('/var/gubbins/cache/')
            ->once()
            ->globally()->ordered();
        $this->logger->expects()
            ->info('Nytris Shift cache warmed')
            ->once()
            ->globally()->ordered();

        $this->cacheDriver->warmUp();
    }

    public function testWarmUpWarmsSourceDirectories(): void
    {
        $this->logger->expects()
            ->info('Warming Nytris Shift cache...')
            ->once()
            ->globally()->ordered();
        $this->logger->expects()
            ->info('Entering directory for Nytris Shift cache warm...', [
                'directory' => 'src',
            ])
            ->once()
            ->globally()->ordered();
        $this->warmer->expects()
            ->warmFile('/my/project/root/src/My/Stuff/MyStuff.php')
            ->once()
            ->globally()->ordered();
        $this->warmer->expects()
            ->warmFile('/my/project/root/src/Your/Gubbins/YourGubbins.php')
            ->once()
            ->globally()->ordered();
        $this->logger->expects()
            ->info('Entering directory for Nytris Shift cache warm...', [
                'directory' => 'stuff',
            ])
            ->once()
            ->globally()->ordered();
        $this->warmer->expects()
            ->warmFile('/my/project/root/stuff/OtherStuff.php')
            ->once()
            ->globally()->ordered();
        $this->logger->expects()
            ->info('Nytris Shift cache warmed')
            ->once()
            ->globally()->ordered();

        $this->cacheDriver->warmUp();
    }

    public function testWarmUpRaisesExceptionWhenSourceDirectoryDoesntExist(): void
    {
        $this->filesystem->allows()
            ->directoryExists('/my/project/root/stuff')
            ->andReturnFalse()
            ->byDefault();

        $this->expectException(DirectoryNotFoundException::class);
        $this->expectExceptionMessage(
            'Cannot warm relative source path "stuff": path "/my/project/root/stuff" does not exist'
        );

        $this->cacheDriver->warmUp();
    }
}
