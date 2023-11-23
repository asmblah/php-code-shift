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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter\Stream;

use Asmblah\PhpCodeShift\Shifter\Stream\Handler\StreamHandlerInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\StreamWrapperManager;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;

/**
 * Class StreamWrapperManagerTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StreamWrapperManagerTest extends AbstractTestCase
{
    public function setUp(): void
    {
        StreamWrapperManager::uninitialise();
    }

    public function tearDown(): void
    {
        StreamWrapperManager::uninitialise();
    }

    public function testInitialiseProvidesStreamHandler(): void
    {
        StreamWrapperManager::initialise();

        static::assertInstanceOf(StreamHandlerInterface::class, StreamWrapperManager::getStreamHandler());
    }

    public function testInitialiseDoesNotReinitialise(): void
    {
        StreamWrapperManager::initialise();
        $streamHandler = StreamWrapperManager::getStreamHandler();

        StreamWrapperManager::initialise();

        static::assertSame($streamHandler, StreamWrapperManager::getStreamHandler());
    }

    public function testSetStreamHandlerOverridesHandler(): void
    {
        $streamHandler = mock(StreamHandlerInterface::class);

        StreamWrapperManager::setStreamHandler($streamHandler);

        static::assertSame($streamHandler, StreamWrapperManager::getStreamHandler());
    }
}
