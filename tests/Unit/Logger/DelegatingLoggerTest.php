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

namespace Asmblah\PhpCodeShift\Tests\Unit\Logger;

use Asmblah\PhpCodeShift\Logger\DelegatingLogger;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Asmblah\PhpCodeShift\Tests\Functional\Util\TestLogger;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

/**
 * Class DelegatingLoggerTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class DelegatingLoggerTest extends AbstractTestCase
{
    private DelegatingLogger $delegatingLogger;

    public function setUp(): void
    {
        $this->delegatingLogger = new DelegatingLogger();
    }

    public function testANullInnerLoggerIsUsedByDefault(): void
    {
        static::assertInstanceOf(NullLogger::class, $this->delegatingLogger->getInnerLogger());
    }

    public function testLogDoesNothingWhenNoLoggerIsSet(): void
    {
        $this->expectNotToPerformAssertions();

        $this->delegatingLogger->log(LogLevel::INFO, 'My message');
    }

    public function testLogDelegatesToInnerLoggerWhenSet(): void
    {
        $innerLogger = new TestLogger();
        $this->delegatingLogger->setInnerLogger($innerLogger);

        $this->delegatingLogger->log(LogLevel::INFO, 'My message', ['hello' => 'world']);

        static::assertEquals(
            [
                ['info', 'My message', ['hello' => 'world']]
            ],
            $innerLogger->getLogs()
        );
    }
}
