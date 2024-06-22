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
use Asmblah\PhpCodeShift\Shifter\Stream\Handler\StreamHandler;
use Asmblah\PhpCodeShift\Shifter\Stream\Shifter\StreamShifterInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Unwrapper\UnwrapperInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Asmblah\PhpCodeShift\Util\CallStackInterface;
use Generator;
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
    private MockInterface&UnwrapperInterface $unwrapper;

    public function setUp(): void
    {
        $this->callStack = mock(CallStackInterface::class);
        $this->statResolver = mock(StatResolverInterface::class);
        $this->streamShifter = mock(StreamShifterInterface::class);
        $this->unwrapper = mock(UnwrapperInterface::class);

        $this->streamHandler = new StreamHandler(
            $this->callStack,
            $this->streamShifter,
            $this->unwrapper,
            $this->statResolver
        );
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
