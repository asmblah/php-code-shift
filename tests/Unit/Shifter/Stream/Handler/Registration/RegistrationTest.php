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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter\Stream\Handler\Registration;

use Asmblah\PhpCodeShift\Shifter\Stream\Handler\Registration\Registration;
use Asmblah\PhpCodeShift\Shifter\Stream\Handler\Registration\RegistrationInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Handler\StreamHandlerInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\StreamWrapperManager;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery\MockInterface;

/**
 * Class RegistrationTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class RegistrationTest extends AbstractTestCase
{
    private MockInterface&StreamHandlerInterface $previousStreamHandler;
    /**
     * @var Registration<StreamHandlerInterface>
     */
    private Registration $registration;
    private MockInterface&StreamHandlerInterface $streamHandler;

    public function setUp(): void
    {
        $this->previousStreamHandler = mock(StreamHandlerInterface::class);
        $this->streamHandler = mock(StreamHandlerInterface::class);

        StreamWrapperManager::uninitialise();
        StreamWrapperManager::initialise();

        $this->registration = new Registration(
            streamHandler: $this->streamHandler,
            previousStreamHandler: $this->previousStreamHandler
        );
    }

    public function tearDown(): void
    {
        StreamWrapperManager::uninitialise();
    }

    public function testGetStreamHandlerReturnsTheRegisteredHandler(): void
    {
        static::assertSame($this->streamHandler, $this->registration->getStreamHandler());
    }

    public function testRedecorateInvokesRedecorateOnTheStreamHandler(): void
    {
        $newWrappedStreamHandler = mock(StreamHandlerInterface::class);

        $this->streamHandler->expects()
            ->redecorate($newWrappedStreamHandler)
            ->once();

        $this->registration->redecorate($newWrappedStreamHandler);
    }

    public function testRegisterSetsTheStreamHandlerOnTheManager(): void
    {
        $this->registration->register();

        static::assertSame($this->streamHandler, StreamWrapperManager::getStreamHandler());
    }

    public function testReplaceReturnsTheProvidedRegistrationByDefault(): void
    {
        $newRegistration = mock(RegistrationInterface::class);

        static::assertSame($newRegistration, $this->registration->replace($newRegistration));
    }

    public function testUnregisterSetsThePreviousStreamHandlerOnTheManagerWhenRegisteredHandlerIsStillCurrent(): void
    {
        $this->registration->register();

        $this->registration->unregister();

        static::assertSame($this->previousStreamHandler, StreamWrapperManager::getStreamHandler());
    }

    public function testUnregisterLeavesManagerUnchangedWhenRegisteredHandlerIsNoLongerCurrent(): void
    {
        $differentStreamHandler = mock(StreamHandlerInterface::class);
        $this->registration->register();
        StreamWrapperManager::setStreamHandler($differentStreamHandler);

        $this->registration->unregister();

        static::assertSame($differentStreamHandler, StreamWrapperManager::getStreamHandler());
    }
}
