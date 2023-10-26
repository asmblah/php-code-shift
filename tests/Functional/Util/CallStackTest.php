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

namespace Asmblah\PhpCodeShift\Tests\Functional\Util;

use Asmblah\PhpCodeShift\CodeShift;
use Asmblah\PhpCodeShift\Shifter\Stream\Handler\StreamHandlerInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\StreamWrapperManager;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Asmblah\PhpCodeShift\Tests\Functional\Harness\StreamHandler\CallStackTestStreamHandler;
use Asmblah\PhpCodeShift\Util\CallStack;

/**
 * Class CallStackTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class CallStackTest extends AbstractTestCase
{
    private CallStack $callStack;
    private CodeShift $codeShift;
    private StreamHandlerInterface $originalStreamHandler;
    private CallStackTestStreamHandler $customStreamHandler;

    public function setUp(): void
    {
        $this->callStack = new CallStack();
        $this->originalStreamHandler = StreamWrapperManager::getStreamHandler();

        $this->customStreamHandler = new CallStackTestStreamHandler(
            $this->originalStreamHandler,
            $this->callStack
        );
        StreamWrapperManager::setStreamHandler($this->customStreamHandler);

        $this->codeShift = new CodeShift();
    }

    public function tearDown(): void
    {
        $this->codeShift->uninstall();

        StreamWrapperManager::setStreamHandler($this->originalStreamHandler);

        clearstatcache();
    }

    /**
     * @dataProvider statBasedFunctionNameProvider
     */
    public function testNativeFunctionNameCanBeFetched(string $functionName): void
    {
        $this->codeShift->install();

        $functionName(__DIR__);

        static::assertSame($functionName, $this->customStreamHandler->getNativeFunctionName());
    }

    /**
     * @return array<string, mixed>
     */
    public static function statBasedFunctionNameProvider(): array
    {
        return [
            'file_exists' => ['file_exists'],
            'is_dir' => ['is_dir'],
            'is_writable' => ['is_writable'],
            // Check aliases too.
            'is_writeable' => ['is_writeable'],
        ];
    }

    // Test this before the non-_once variant as otherwise the file will not be included a second time.
    public function testIncludeOnceCanBeFetched(): void
    {
        $this->codeShift->install();

        static::assertSame(21, include_once __DIR__ . '/../Fixtures/return_21.php');
        static::assertSame('include_once', $this->customStreamHandler->getNativeFunctionName());
    }

    public function testIncludeCanBeFetched(): void
    {
        $this->codeShift->install();

        static::assertSame(21, include __DIR__ . '/../Fixtures/return_21.php');
        static::assertSame('include', $this->customStreamHandler->getNativeFunctionName());
    }

    public function testRequireOnceCanBeFetched(): void
    {
        $this->codeShift->install();

        // Note use of a different module to the include* tests
        // as otherwise for the -_once variant it will not be included a second time.
        static::assertSame(22, require_once __DIR__ . '/../Fixtures/return_22.php');
        static::assertSame('require_once', $this->customStreamHandler->getNativeFunctionName());
    }

    public function testRequireCanBeFetched(): void
    {
        $this->codeShift->install();

        static::assertSame(22, require __DIR__ . '/../Fixtures/return_22.php');
        static::assertSame('require', $this->customStreamHandler->getNativeFunctionName());
    }
}
