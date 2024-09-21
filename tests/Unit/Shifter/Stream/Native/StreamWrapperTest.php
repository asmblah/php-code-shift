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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter\Stream\Native;

use Asmblah\PhpCodeShift\Shifter\Stream\Handler\StreamHandlerInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Native\StreamWrapper;
use Asmblah\PhpCodeShift\Shifter\Stream\StreamWrapperManager;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery\MockInterface;

/**
 * Class StreamWrapperTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StreamWrapperTest extends AbstractTestCase
{
    private MockInterface&StreamHandlerInterface $streamHandler;
    private StreamWrapper $streamWrapper;

    public function setUp(): void
    {
        StreamWrapperManager::uninitialise();
        StreamWrapperManager::initialise();

        $this->streamHandler = mock(StreamHandlerInterface::class);
        StreamWrapperManager::setStreamHandler($this->streamHandler);

        $this->streamWrapper = new StreamWrapper();
    }

    public function tearDown(): void
    {
        StreamWrapperManager::uninitialise();
    }

    public function testStreamOpenCorrectlyOpensAnIncludeFileReadStream(): void
    {
        $stream = fopen('php://memory', 'rb+');
        $openedPath = null;
        $this->streamHandler->allows()
            ->streamOpen(
                $this->streamWrapper,
                '/my/path/to/my_module.php',
                'rb',
                StreamHandlerInterface::STREAM_OPEN_FOR_INCLUDE,
                $openedPath
            )
            ->andReturnUsing(function ($_1, $path, $_3, $_4, &$openedPath) use ($stream) {
                $openedPath = $path;

                return [
                    'isInclude' => true,
                    'resource' => $stream,
                ];
            });

        static::assertTrue(
            $this->streamWrapper->stream_open(
                path: '/my/path/to/my_module.php',
                mode: 'rb',
                options: StreamHandlerInterface::STREAM_OPEN_FOR_INCLUDE,
                openedPath: $openedPath
            )
        );
        static::assertTrue($this->streamWrapper->isInclude());
        static::assertSame($stream, $this->streamWrapper->getWrappedResource());
        static::assertSame('rb', $this->streamWrapper->getOpenMode());
        static::assertSame('/my/path/to/my_module.php', $this->streamWrapper->getOpenPath());
        static::assertSame('/my/path/to/my_module.php', $openedPath);
    }

    public function testStreamOpenCorrectlyOpensAPlainFileReadStream(): void
    {
        $stream = fopen('php://memory', 'rb+');
        $openedPath = null;
        $this->streamHandler->allows()
            ->streamOpen(
                $this->streamWrapper,
                '/my/path/to/my_module.php',
                'rb',
                0,
                $openedPath
            )
            ->andReturnUsing(function ($_1, $path, $_3, $_4, &$openedPath) use ($stream) {
                $openedPath = $path;

                return [
                    'isInclude' => false,
                    'resource' => $stream,
                ];
            });

        static::assertTrue(
            $this->streamWrapper->stream_open(
                path: '/my/path/to/my_module.php',
                mode: 'rb',
                options: 0,
                openedPath: $openedPath
            )
        );
        static::assertFalse($this->streamWrapper->isInclude());
        static::assertSame($stream, $this->streamWrapper->getWrappedResource());
        static::assertSame('rb', $this->streamWrapper->getOpenMode());
        static::assertSame('/my/path/to/my_module.php', $this->streamWrapper->getOpenPath());
        static::assertSame('/my/path/to/my_module.php', $openedPath);
    }
}
