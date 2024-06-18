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

namespace Asmblah\PhpCodeShift\Tests\Unit\Filesystem\Stat;

use Asmblah\PhpCodeShift\Filesystem\Stat\NativeStatResolver;
use Asmblah\PhpCodeShift\Shifter\Stream\Unwrapper\UnwrapperInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery\MockInterface;

/**
 * Class NativeStatResolverTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class NativeStatResolverTest extends AbstractTestCase
{
    private NativeStatResolver $resolver;
    private MockInterface&UnwrapperInterface $unwrapper;

    public function setUp(): void
    {
        $this->unwrapper = mock(UnwrapperInterface::class);

        $this->unwrapper->allows('unwrapped')
            ->andReturnUsing(static fn (callable $callback) => $callback())
            ->byDefault();

        $this->resolver = new NativeStatResolver($this->unwrapper);
    }

    public function testStatReturnsAValidStatForExistentFiles(): void
    {
        $stat = $this->resolver->stat(__FILE__, false, false);

        static::assertIsArray($stat);
        static::assertArrayHasKey('gid', $stat);
        static::assertArrayHasKey('mode', $stat);
        static::assertArrayHasKey('uid', $stat);
    }

    public function testStatReturnsNullForNonExistentFiles(): void
    {
        static::assertNull(@$this->resolver->stat(__DIR__ . '/non/existent/file', false, false));
    }
}
