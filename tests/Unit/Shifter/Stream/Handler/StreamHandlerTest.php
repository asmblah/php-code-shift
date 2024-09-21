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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter\Stream\Handler;

use Asmblah\PhpCodeShift\Filesystem\Stat\StatResolverInterface;
use Asmblah\PhpCodeShift\Shift;
use Asmblah\PhpCodeShift\Shifter\Stream\Handler\StreamHandler;
use Asmblah\PhpCodeShift\Shifter\Stream\Handler\StreamHandlerInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Native\StreamWrapperInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Shifter\StreamShifterInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Unwrapper\UnwrapperInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Asmblah\PhpCodeShift\Util\CallStackInterface;
use Generator;
use Mockery;
use Mockery\MockInterface;

/**
 * Class StreamHandlerTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StreamHandlerTest extends AbstractTestCase
{
    private MockInterface&CallStackInterface $callStack;
    private MockInterface&StatResolverInterface $statResolver;
    private StreamHandler $streamHandler;
    private MockInterface&StreamShifterInterface $streamShifter;
    private MockInterface&StreamWrapperInterface $streamWrapper;
    private MockInterface&UnwrapperInterface $unwrapper;

    public function setUp(): void
    {
        Shift::uninstall();

        $this->callStack = mock(CallStackInterface::class);
        $this->statResolver = mock(StatResolverInterface::class);
        $this->streamShifter = mock(StreamShifterInterface::class);
        $this->streamWrapper = mock(StreamWrapperInterface::class, [
            'getContext' => null,
        ]);
        $this->unwrapper = mock(UnwrapperInterface::class);

        $this->unwrapper->allows('unwrapped')
            ->andReturnUsing(fn (callable $callback) => $callback())
            ->byDefault();

        $this->streamHandler = new StreamHandler(
            $this->callStack,
            $this->streamShifter,
            $this->unwrapper,
            $this->statResolver
        );
    }

    public function tearDown(): void
    {
        Shift::uninstall();
    }

    public function testIsIncludeReturnsFalseForPlainFileRead(): void
    {
        static::assertFalse($this->streamHandler->isInclude(0));
    }

    public function testIsIncludeReturnsTrueForIncludeRead(): void
    {
        $this->callStack->allows()
            ->getNativeFunctionName()
            ->andReturn('myFunction');

        static::assertTrue(
            $this->streamHandler->isInclude(
                StreamHandlerInterface::STREAM_OPEN_FOR_INCLUDE
            )
        );
    }

    public function testIsIncludeReturnsFalseForParseIniFile(): void
    {
        $this->callStack->allows()
            ->getNativeFunctionName()
            ->andReturn('parse_ini_file');

        static::assertFalse(
            $this->streamHandler->isInclude(
                StreamHandlerInterface::STREAM_OPEN_FOR_INCLUDE
            )
        );
    }

    public function testShiftFileShiftsViaStreamShifter(): void
    {
        $originalStream = fopen('php://memory', 'rb+');
        $shiftedStream = fopen('php://memory', 'rb+');

        $this->streamShifter->expects()
            ->shift(
                '/my/path/to/my_file.php',
                Mockery::on(fn (callable $openStream) => $openStream() === $originalStream)
            )
            ->once()
            ->andReturn($shiftedStream);

        static::assertSame(
            $shiftedStream,
            $this->streamHandler->shiftFile(
                '/my/path/to/my_file.php',
                fn () => $originalStream
            )
        );
    }

    public function testStreamOpenReturnsCorrectResultForPlainFileRead(): void
    {
        $result = $this->streamHandler->streamOpen(
            $this->streamWrapper,
            path: __FILE__,
            mode: 'r',
            options: 0,
            openedPath: $openedPath
        );

        static::assertIsResource($result['resource']);
        static::assertFalse($result['isInclude']);
    }

    public function testStreamOpenReturnsCorrectResultForIncludeRead(): void
    {
        $this->callStack->allows()
            ->getNativeFunctionName()
            ->andReturn('myFunction');
        $shiftedResource = fopen('php://memory', 'rb+');
        $this->streamShifter->allows('shift')
            ->andReturn($shiftedResource);

        $result = $this->streamHandler->streamOpen(
            $this->streamWrapper,
            path: __FILE__,
            mode: 'r',
            options: StreamHandlerInterface::STREAM_OPEN_FOR_INCLUDE,
            openedPath: $openedPath
        );

        static::assertSame($shiftedResource, $result['resource']);
        static::assertTrue($result['isInclude']);
    }

    public function testStreamOpenOpensUnderlyingStreamForPlainFileReadViaUnwrapper(): void
    {
        $underlyingStream = null;
        $unwrappedStream = fopen('php://memory', 'rb+');
        $this->unwrapper->allows('unwrapped')
            ->andReturnUsing(function (callable $callback) use (&$underlyingStream, $unwrappedStream) {
                $underlyingStream = $callback();

                return $unwrappedStream;
            });

        $result = $this->streamHandler->streamOpen(
            $this->streamWrapper,
            path: __FILE__,
            mode: 'r',
            options: 0,
            openedPath: $openedPath
        );

        static::assertSame($unwrappedStream, $result['resource']);
        $metaData = stream_get_meta_data($underlyingStream);
        static::assertSame('plainfile', $metaData['wrapper_type']);
    }

    public function testStreamOpenOpensUnderlyingStreamForIncludeReadViaUnwrapper(): void
    {
        $underlyingStream = null;
        $unwrappedStream = fopen('php://memory', 'rb+');
        $this->unwrapper->allows('unwrapped')
            ->andReturnUsing(function (callable $callback) use (&$underlyingStream, $unwrappedStream) {
                $underlyingStream = $callback();

                return $unwrappedStream;
            });
        $this->callStack->allows()
            ->getNativeFunctionName()
            ->andReturn('myFunction');
        $shiftedResource = fopen('php://memory', 'rb+');
        $this->streamShifter->allows()
            ->shift(
                __FILE__,
                Mockery::on(fn ($openStream) => $openStream() === $unwrappedStream)
            )
            ->andReturn($shiftedResource);

        $result = $this->streamHandler->streamOpen(
            $this->streamWrapper,
            path: __FILE__,
            mode: 'r',
            options: StreamHandlerInterface::STREAM_OPEN_FOR_INCLUDE,
            openedPath: $openedPath
        );

        static::assertSame($shiftedResource, $result['resource']);
        $metaData = stream_get_meta_data($underlyingStream);
        static::assertSame('plainfile', $metaData['wrapper_type']);
    }

    public function testStreamOpenDoesNotShiftForParseIniFile(): void
    {
        $underlyingStream = null;
        $unwrappedStream = fopen('php://memory', 'rb+');
        $this->unwrapper->allows('unwrapped')
            ->andReturnUsing(function (callable $callback) use (&$underlyingStream, $unwrappedStream) {
                $underlyingStream = $callback();

                return $unwrappedStream;
            });
        $this->callStack->allows()
            ->getNativeFunctionName()
            ->andReturn('parse_ini_file');

        $this->streamShifter->expects('shift')
            ->never();

        $result = $this->streamHandler->streamOpen(
            $this->streamWrapper,
            path: __FILE__,
            mode: 'r',
            options: StreamHandlerInterface::STREAM_OPEN_FOR_INCLUDE,
            openedPath: $openedPath
        );

        static::assertSame($unwrappedStream, $result['resource']);
        $metaData = stream_get_meta_data($underlyingStream);
        static::assertSame('plainfile', $metaData['wrapper_type']);
    }

    public function testUnwrappedForwardsOntoUnwrapper(): void
    {
        $this->unwrapper->allows('unwrapped')
            ->andReturnUsing(static fn (callable $callback) => $callback() . ' [unwrapped]')
            ->byDefault();

        static::assertEquals(
            'hello [unwrapped]',
            $this->streamHandler->unwrapped(static fn () => 'hello')
        );
    }

    /**
     * @dataProvider urlStatProvider
     */
    public function testUrlStatForwardsOntoStatResolver(bool $link, bool $quiet, int $flags): void
    {
        $this->statResolver->allows()
            ->stat('/my/path', $link, $quiet)
            ->andReturn(['mode' => 0555]);

        static::assertEquals(['mode' => 0555], $this->streamHandler->urlStat('/my/path', $flags));
    }

    public static function urlStatProvider(): Generator
    {
        yield [false, false, 0];
        yield [false, true, STREAM_URL_STAT_QUIET];
        yield [true, false, STREAM_URL_STAT_LINK];
        yield [true, true, STREAM_URL_STAT_LINK | STREAM_URL_STAT_QUIET];
    }
}
