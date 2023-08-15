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

namespace Asmblah\PhpCodeShift\Tests\Functional;

use Asmblah\PhpCodeShift\CodeShift;
use Asmblah\PhpCodeShift\Shifter\Stream\Handler\AbstractStreamHandlerDecorator;
use Asmblah\PhpCodeShift\Shifter\Stream\Handler\StreamHandlerInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\StreamWrapperManager;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;

/**
 * Class StreamHandlerDecorationTest.
 *
 * Tests decoration or replacement of the stream handler mechanism.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StreamHandlerDecorationTest extends AbstractTestCase
{
    private ?CodeShift $codeShift;
    private ?StreamHandlerInterface $originalStreamHandler;

    public function setUp(): void
    {
        $this->originalStreamHandler = StreamWrapperManager::getStreamHandler();

        $this->codeShift = new CodeShift();
    }

    public function tearDown(): void
    {
        $this->codeShift->uninstall();

        StreamWrapperManager::setStreamHandler($this->originalStreamHandler);
    }

    public function testStreamHandlerCanBeDecorated(): void
    {
        StreamWrapperManager::setStreamHandler(
            new class($this->originalStreamHandler) extends AbstractStreamHandlerDecorator {
                /**
                 * @inheritDoc
                 */
                public function urlStat(string $path, int $flags): array|false
                {
                    return $path === __FILE__ ? false : parent::urlStat($path, $flags);
                }
            }
        );
        $this->codeShift->install();

        static::assertFalse(file_exists(__FILE__));
        static::assertTrue(is_dir(__DIR__));
    }
}
