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

namespace Asmblah\PhpCodeShift\Tests\Unit;

use Asmblah\PhpCodeShift\Bootstrap\BootstrapInterface;
use Asmblah\PhpCodeShift\Filesystem\Stat\AclStatResolver;
use Asmblah\PhpCodeShift\Filesystem\Stat\StatResolverInterface;
use Asmblah\PhpCodeShift\Shared;
use Asmblah\PhpCodeShift\Shifter\Stream\Unwrapper\Unwrapper;
use Asmblah\PhpCodeShift\Shifter\Stream\Unwrapper\UnwrapperInterface;
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

        static::assertInstanceOf(NullLogger::class, Shared::getLogger()->getInnerLogger());
    }

    public function testInitialiseProvidesAclStatResolver(): void
    {
        Shared::initialise();

        static::assertInstanceOf(AclStatResolver::class, Shared::getStatResolver());
    }

    public function testInitialiseProvidesUnwrapper(): void
    {
        Shared::initialise();

        static::assertInstanceOf(Unwrapper::class, Shared::getUnwrapper());
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

        static::assertSame($logger, Shared::getLogger()->getInnerLogger());
    }

    public function testSetStatResolverOverridesStatResolver(): void
    {
        $statResolver = mock(StatResolverInterface::class);

        Shared::setStatResolver($statResolver);

        static::assertSame($statResolver, Shared::getStatResolver());
    }

    public function testSetUnwrapperOverridesUnwrapper(): void
    {
        $unwrapper = mock(UnwrapperInterface::class);

        Shared::setUnwrapper($unwrapper);

        static::assertSame($unwrapper, Shared::getUnwrapper());
    }
}
