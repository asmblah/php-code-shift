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

namespace Asmblah\PhpCodeShift\Tests\Unit\Cache\Warmer;

use Asmblah\PhpCodeShift\Cache\Adapter\FilesystemCacheAdapterInterface;
use Asmblah\PhpCodeShift\Cache\Warmer\FilesystemCacheWarmer;
use Asmblah\PhpCodeShift\Exception\FileNotCachedException;
use Asmblah\PhpCodeShift\Exception\ParseFailedException;
use Asmblah\PhpCodeShift\Filesystem\FilesystemInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Shifter\ShiftSetShifterInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\ShiftSetInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Resolver\ShiftSetResolverInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery\MockInterface;
use PhpParser\Error;
use Psr\Log\LoggerInterface;

/**
 * Class FilesystemCacheWarmerTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class FilesystemCacheWarmerTest extends AbstractTestCase
{
    private MockInterface&FilesystemCacheAdapterInterface $cacheAdapter;
    private MockInterface&FilesystemInterface $filesystem;
    private MockInterface&LoggerInterface $logger;
    private MockInterface&ShiftSetInterface $shiftSet;
    private MockInterface&ShiftSetResolverInterface $shiftSetResolver;
    private MockInterface&ShiftSetShifterInterface $shiftSetShifter;
    private FilesystemCacheWarmer $warmer;

    public function setUp(): void
    {
        $this->cacheAdapter = mock(FilesystemCacheAdapterInterface::class, [
            'saveFile' => null,
        ]);
        $this->filesystem = mock(FilesystemInterface::class, [
            'directoryExists' => false,
            'mkdir' => null,
        ]);
        $this->logger = mock(LoggerInterface::class, [
            'info' => null,
        ]);
        $this->shiftSet = mock(ShiftSetInterface::class);
        $this->shiftSetResolver = mock(ShiftSetResolverInterface::class);
        $this->shiftSetShifter = mock(ShiftSetShifterInterface::class);

        $this->filesystem->allows('readFile')
            ->andReturnUsing(fn (string $path) => "<?php return 'The contents of \\'$path\\' are this.';")
            ->byDefault();

        $this->shiftSetResolver->allows()
            ->resolveShiftSet('/path/to/project/src/My/Stuff/MyStuff.php')
            ->andReturn($this->shiftSet)
            ->byDefault();
        $this->shiftSetShifter->allows()
            ->shift('<?php return \'The contents of \\\'/path/to/project/src/My/Stuff/MyStuff.php\\\' are this.\';', $this->shiftSet)
            ->andReturn('<?php return "my shifted code is this.";')
            ->byDefault();

        $this->warmer = new FilesystemCacheWarmer(
            $this->cacheAdapter,
            $this->filesystem,
            $this->shiftSetResolver,
            $this->shiftSetShifter,
            $this->logger
        );
    }

    public function testWarmFileSavesViaAdapter(): void
    {
        $this->cacheAdapter->expects()
            ->saveFile('/path/to/project/src/My/Stuff/MyStuff.php', '<?php return "my shifted code is this.";')
            ->once();
        $this->logger->expects()
            ->info('Nytris Shift successfully warmed cache file', [
                'path' => '/path/to/project/src/My/Stuff/MyStuff.php',
            ])
            ->once();

        $this->warmer->warmFile('/path/to/project/src/My/Stuff/MyStuff.php');
    }

    public function testWarmFileDoesNothingWhenNoShiftsApply(): void
    {
        $this->shiftSetResolver->allows()
            ->resolveShiftSet('/path/to/project/src/My/Stuff/MyStuff.php')
            ->andReturnNull();

        $this->filesystem->expects('readFile')
            ->never();
        $this->cacheAdapter->expects('saveFile')
            ->never();
        $this->logger->expects('info')
            ->never();

        $this->warmer->warmFile('/path/to/project/src/My/Stuff/MyStuff.php');
    }

    public function testWarmFileLogsWarningWhenShiftFailsWithSyntaxError(): void
    {
        $this->shiftSetShifter->allows()
            ->shift('<?php return \'The contents of \\\'/path/to/project/src/My/Stuff/MyStuff.php\\\' are this.\';', $this->shiftSet)
            ->andThrow(
                new ParseFailedException(
                    '/path/to/project/src/My/Stuff/MyStuff.php',
                    new Error('Bang!', ['startLine' => 21])
                )
            );

        $this->logger->expects()
            ->warning('Nytris Shift failed to shift file', [
                'path' => '/path/to/project/src/My/Stuff/MyStuff.php',
                'exception' => [
                    'class' => Error::class,
                    'message' => 'Failed to parse path "/path/to/project/src/My/Stuff/MyStuff.php" :: PhpParser\Error "Bang! on line 21"',
                ],
            ])
            ->once();
        $this->cacheAdapter->expects('saveFile')
            ->never();
        $this->logger->expects('info')
            ->never();

        $this->warmer->warmFile('/path/to/project/src/My/Stuff/MyStuff.php');
    }

    public function testWarmFileLogsErrorWhenAdapterFailsToSaveFile(): void
    {
        $this->cacheAdapter->allows()
            ->saveFile('/path/to/project/src/My/Stuff/MyStuff.php', '<?php return "my shifted code is this.";')
            ->andThrow(new FileNotCachedException('Bang!'));

        $this->logger->expects()
            ->error('Nytris Shift failed to save cache file', [
                'path' => '/path/to/project/src/My/Stuff/MyStuff.php',
                'exception' => [
                    'message' => 'Bang!',
                ],
            ])
            ->once();
        $this->logger->expects('info')
            ->never();

        $this->warmer->warmFile('/path/to/project/src/My/Stuff/MyStuff.php');
    }
}
