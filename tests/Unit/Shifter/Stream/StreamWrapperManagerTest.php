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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter\Stream;

use Asmblah\PhpCodeShift\Shifter\Stream\Handler\Registration\RegistrantInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Handler\Registration\RegistrationInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Handler\StreamHandlerInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\StreamWrapperManager;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use LogicException;
use Mockery\MockInterface;

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

    public function testIsInitialisedReturnsTrueWhenInitialised(): void
    {
        StreamWrapperManager::initialise();

        static::assertTrue(StreamWrapperManager::isInitialised());
    }

    public function testIsInitialisedReturnsFalseWhenNotYetInitialised(): void
    {
        static::assertFalse(StreamWrapperManager::isInitialised());
    }

    public function testIsInitialisedReturnsFalseWhenInitialisedThenUninitialised(): void
    {
        StreamWrapperManager::initialise();
        StreamWrapperManager::uninitialise();

        static::assertFalse(StreamWrapperManager::isInitialised());
    }

    public function testRegisterStreamHandlerPerformsFirstRegistrationCorrectly(): void
    {
        $newStreamHandler = mock(StreamHandlerInterface::class);
        /** @var MockInterface&RegistrantInterface<StreamHandlerInterface> $registrant */
        $registrant = mock(RegistrantInterface::class);
        /** @var MockInterface&RegistrationInterface<StreamHandlerInterface> $registration */
        $registration = mock(RegistrationInterface::class, [
            'getStreamHandler' => $newStreamHandler,
        ]);
        StreamWrapperManager::initialise();

        $registrant->expects()
            ->registerStreamHandler(
                StreamWrapperManager::getStreamHandler(),
                null
            )
            ->once()
            ->andReturn($registration);
        $registration->expects()
            ->register()
            ->once();
        static::assertSame(
            $registration,
            StreamWrapperManager::registerStreamHandler($registrant)
        );
    }

    public function testRegisterStreamHandlerPerformsSecondRegistrationCorrectly(): void
    {
        $newStreamHandler1 = mock(StreamHandlerInterface::class);
        $newStreamHandler2 = mock(StreamHandlerInterface::class);
        $newStreamHandler3 = mock(StreamHandlerInterface::class);
        /** @var MockInterface&RegistrantInterface<StreamHandlerInterface> $registrant1 */
        $registrant1 = mock(RegistrantInterface::class);
        /** @var MockInterface&RegistrantInterface<StreamHandlerInterface> $registrant2 */
        $registrant2 = mock(RegistrantInterface::class);
        /** @var MockInterface&RegistrationInterface<StreamHandlerInterface> $registration1 */
        $registration1 = mock(RegistrationInterface::class, [
            'getStreamHandler' => $newStreamHandler1,
        ]);
        /** @var MockInterface&RegistrationInterface<StreamHandlerInterface> $registration2 */
        $registration2 = mock(RegistrationInterface::class, [
            'getStreamHandler' => $newStreamHandler2,
        ]);
        /** @var MockInterface&RegistrationInterface<StreamHandlerInterface> $registration3 */
        $registration3 = mock(RegistrationInterface::class, [
            'getStreamHandler' => $newStreamHandler3,
        ]);
        StreamWrapperManager::initialise();
        $registrant1->allows()
            ->registerStreamHandler(
                StreamWrapperManager::getStreamHandler(),
                null
            )
            ->andReturn($registration1);
        $registration1->allows()
            ->register();
        // Perform first registration.
        StreamWrapperManager::registerStreamHandler($registrant1);

        // Check that second registration's replace method is able to manipulate
        // by returning a completely different registration.
        $registration1->expects()
            ->replace($registration2)
            ->once()
            ->andReturn($registration3);
        $registrant2->expects()
            ->registerStreamHandler(
                StreamWrapperManager::getStreamHandler(),
                null
            )
            ->once()
            ->andReturn($registration2);
        $registration3->expects()
            ->register()
            ->once();
        static::assertSame(
            $registration3,
            // Perform second registration.
            StreamWrapperManager::registerStreamHandler($registrant2)
        );
    }

    public function testRegisterStreamHandlerThrowsExceptionWhenNotYetInitialised(): void
    {
        /** @var MockInterface&RegistrantInterface<StreamHandlerInterface> $registrant */
        $registrant = mock(RegistrantInterface::class);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Not initialised');

        StreamWrapperManager::registerStreamHandler($registrant);
    }

    public function testSetStreamHandlerOverridesHandler(): void
    {
        $streamHandler = mock(StreamHandlerInterface::class);

        StreamWrapperManager::setStreamHandler($streamHandler);

        static::assertSame($streamHandler, StreamWrapperManager::getStreamHandler());
    }
}
