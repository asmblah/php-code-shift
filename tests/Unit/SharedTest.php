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

namespace Asmblah\PhpCodeShift\Tests\Unit;

use Asmblah\PhpCodeShift\Bootstrap\BootstrapInterface;
use Asmblah\PhpCodeShift\Shared;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Asmblah\PhpCodeShift\Util\CallStackInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class SharedTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class SharedTest extends AbstractTestCase
{
    public function setUp(): void
    {
        Shared::uninitialise();
        Shared::initialise();
    }

    public function tearDown(): void
    {
        Shared::uninitialise();
        Shared::initialise();
    }

    public function testInitialiseProvidesBootstrap(): void
    {
        Shared::initialise();

        static::assertInstanceOf(BootstrapInterface::class, Shared::getBootstrap());
    }

    public function testInitialiseProvidesCallStack(): void
    {
        Shared::initialise();

        static::assertInstanceOf(CallStackInterface::class, Shared::getCallStack());
    }

    public function testInitialiseProvidesNullLogger(): void
    {
        Shared::initialise();

        static::assertInstanceOf(NullLogger::class, Shared::getLogger());
    }

    public function testInitialiseDoesNotReinitialise(): void
    {
        Shared::initialise();
        $callStack = Shared::getCallStack();

        Shared::initialise();

        static::assertSame($callStack, Shared::getCallStack());
    }

    public function testSetBootstrapOverridesBootstrap(): void
    {
        $bootstrap = mock(BootstrapInterface::class);

        Shared::setBootstrap($bootstrap);

        static::assertSame($bootstrap, Shared::getBootstrap());
    }

    public function testSetCallStackOverridesCallStack(): void
    {
        $callStack = mock(CallStackInterface::class);

        Shared::setCallStack($callStack);

        static::assertSame($callStack, Shared::getCallStack());
    }

    public function testSetLoggerOverridesLogger(): void
    {
        $logger = mock(LoggerInterface::class);

        Shared::setLogger($logger);

        static::assertSame($logger, Shared::getLogger());
    }
}
