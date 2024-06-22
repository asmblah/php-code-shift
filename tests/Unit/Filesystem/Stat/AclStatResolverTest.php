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

namespace Asmblah\PhpCodeShift\Tests\Unit\Filesystem\Stat;

use Asmblah\PhpCodeShift\Filesystem\Access\AccessResolverInterface;
use Asmblah\PhpCodeShift\Filesystem\Stat\AclStatResolver;
use Asmblah\PhpCodeShift\Filesystem\Stat\StatResolverInterface;
use Asmblah\PhpCodeShift\Posix\PosixInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Generator;
use Mockery\MockInterface;

/**
 * Class AclStatResolverTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class AclStatResolverTest extends AbstractTestCase
{
    private MockInterface&AccessResolverInterface $accessResolver;
    private MockInterface&PosixInterface $posix;
    private AclStatResolver $resolver;
    private MockInterface&StatResolverInterface $statResolver;

    public function setUp(): void
    {
        $this->accessResolver = mock(AccessResolverInterface::class, [
            'isExecutable' => false,
            'isReadable' => false,
            'isWritable' => false,
        ]);
        $this->posix = mock(PosixInterface::class, [
            'getGroupId' => 123,
            'getGroupSet' => [21, 101],
            'getUserId' => 456,
            'isPosixAvailable' => true,
        ]);
        $this->statResolver = mock(StatResolverInterface::class);

        $this->statResolver->allows()
            ->stat('/path/to/my_file', true, false)
            ->andReturn([
                'gid' => 123,
                'mode' => 0755,
                'uid' => 456,
            ])
            ->byDefault();

        $this->resolver = new AclStatResolver($this->statResolver, $this->posix, $this->accessResolver);
    }

    public function testStatReturnsStatusFromUnderlyingResolverUnmodifiedIfNotAccessible(): void
    {
        static::assertEquals(
            [
                'gid' => 123,
                'mode' => 0755,
                'uid' => 456,
            ],
            $this->resolver->stat('/path/to/my_file', true, false)
        );
    }

    public function testStatReturnsNullWhenUnderlyingResolverReturnsNullOnFailure(): void
    {
        $this->statResolver->allows()
            ->stat('/path/to/my_file', true, false)
            ->andReturnNull();

        static::assertNull($this->resolver->stat('/path/to/my_file', true, false));
    }

    /**
     * @dataProvider executablePermissionTweakingProvider
     *
     * @param string $path
     * @param array<mixed> $statFromUnderlyingResolver
     * @param array<mixed> $expectedResultStat
     * @param bool $link
     * @param bool $quiet
     */
    public function testStatTweaksCorrectPermissionBitsWhenExecutableWithPosixAvailable(
        string $path,
        array $statFromUnderlyingResolver,
        array $expectedResultStat,
        bool $link,
        bool $quiet
    ): void {
        $this->statResolver->allows()
            ->stat($path, true, false)
            ->andReturn($statFromUnderlyingResolver);
        $this->accessResolver->allows()
            ->isExecutable($path)
            ->andReturnTrue();

        static::assertEquals(
            $expectedResultStat,
            $this->resolver->stat($path, link: $link, quiet: $quiet)
        );
    }

    public static function executablePermissionTweakingProvider(): Generator
    {
        yield 'tweaks user permission when running as owning user' => [
            '/path/to/my_file',
            [
                'gid' => 123,
                'mode' => 0444,
                'uid' => 456,
            ],
            [
                'gid' => 123,
                'mode' => 0544,
                'uid' => 456,
            ],
            true,
            false
        ];

        yield 'tweaks group permission when running as owning group' => [
            '/path/to/my_file',
            [
                'gid' => 123,
                'mode' => 0444,
                'uid' => 888,
            ],
            [
                'gid' => 123,
                'mode' => 0454,
                'uid' => 888,
            ],
            true,
            false
        ];

        yield 'tweaks group permission when running as another group of current user' => [
            '/path/to/my_file',
            [
                'gid' => 101,
                'mode' => 0444,
                'uid' => 888,
            ],
            [
                'gid' => 101,
                'mode' => 0454,
                'uid' => 888,
            ],
            true,
            false
        ];

        yield 'tweaks other/world permission when running as neither owning user nor group' => [
            '/path/to/my_file',
            [
                'gid' => 999,
                'mode' => 0444,
                'uid' => 888,
            ],
            [
                'gid' => 999,
                'mode' => 0445,
                'uid' => 888,
            ],
            true,
            false
        ];
    }

    /**
     * @dataProvider readablePermissionTweakingProvider
     *
     * @param string $path
     * @param array<mixed> $statFromUnderlyingResolver
     * @param array<mixed> $expectedResultStat
     * @param bool $link
     * @param bool $quiet
     */
    public function testStatTweaksCorrectPermissionBitsWhenReadableWithPosixAvailable(
        string $path,
        array $statFromUnderlyingResolver,
        array $expectedResultStat,
        bool $link,
        bool $quiet
    ): void {
        $this->statResolver->allows()
            ->stat($path, true, false)
            ->andReturn($statFromUnderlyingResolver);
        $this->accessResolver->allows()
            ->isReadable($path)
            ->andReturnTrue();

        static::assertEquals(
            $expectedResultStat,
            $this->resolver->stat($path, link: $link, quiet: $quiet)
        );
    }

    public static function readablePermissionTweakingProvider(): Generator
    {
        yield 'tweaks user permission when running as owning user' => [
            '/path/to/my_file',
            [
                'gid' => 123,
                'mode' => 0111,
                'uid' => 456,
            ],
            [
                'gid' => 123,
                'mode' => 0511,
                'uid' => 456,
            ],
            true,
            false
        ];

        yield 'tweaks group permission when running as owning group' => [
            '/path/to/my_file',
            [
                'gid' => 123,
                'mode' => 0111,
                'uid' => 888,
            ],
            [
                'gid' => 123,
                'mode' => 0151,
                'uid' => 888,
            ],
            true,
            false
        ];

        yield 'tweaks group permission when running as another group of current user' => [
            '/path/to/my_file',
            [
                'gid' => 21,
                'mode' => 0111,
                'uid' => 888,
            ],
            [
                'gid' => 21,
                'mode' => 0151,
                'uid' => 888,
            ],
            true,
            false
        ];

        yield 'tweaks other/world permission when running as neither owning user nor group' => [
            '/path/to/my_file',
            [
                'gid' => 999,
                'mode' => 0111,
                'uid' => 888,
            ],
            [
                'gid' => 999,
                'mode' => 0115,
                'uid' => 888,
            ],
            true,
            false
        ];
    }

    /**
     * @dataProvider writablePermissionTweakingProvider
     *
     * @param string $path
     * @param array<mixed> $statFromUnderlyingResolver
     * @param array<mixed> $expectedResultStat
     * @param bool $link
     * @param bool $quiet
     */
    public function testStatTweaksCorrectPermissionBitsWhenWritableWithPosixAvailable(
        string $path,
        array $statFromUnderlyingResolver,
        array $expectedResultStat,
        bool $link,
        bool $quiet
    ): void {
        $this->statResolver->allows()
            ->stat($path, true, false)
            ->andReturn($statFromUnderlyingResolver);
        $this->accessResolver->allows()
            ->isWritable($path)
            ->andReturnTrue();

        static::assertEquals(
            $expectedResultStat,
            $this->resolver->stat($path, link: $link, quiet: $quiet)
        );
    }

    public static function writablePermissionTweakingProvider(): Generator
    {
        yield 'tweaks user permission when running as owning user' => [
            '/path/to/my_file',
            [
                'gid' => 123,
                'mode' => 0111,
                'uid' => 456,
            ],
            [
                'gid' => 123,
                'mode' => 0311,
                'uid' => 456,
            ],
            true,
            false
        ];

        yield 'tweaks group permission when running as owning group' => [
            '/path/to/my_file',
            [
                'gid' => 123,
                'mode' => 0111,
                'uid' => 888,
            ],
            [
                'gid' => 123,
                'mode' => 0131,
                'uid' => 888,
            ],
            true,
            false
        ];

        yield 'tweaks group permission when running as another group of current user' => [
            '/path/to/my_file',
            [
                'gid' => 101,
                'mode' => 0111,
                'uid' => 888,
            ],
            [
                'gid' => 101,
                'mode' => 0131,
                'uid' => 888,
            ],
            true,
            false
        ];

        yield 'tweaks other/world permission when running as neither owning user nor group' => [
            '/path/to/my_file',
            [
                'gid' => 999,
                'mode' => 0111,
                'uid' => 888,
            ],
            [
                'gid' => 999,
                'mode' => 0113,
                'uid' => 888,
            ],
            true,
            false
        ];
    }

    public function testStatTweaksWorldPermissionBitsWhenExecutableWithoutPosixAvailable(): void
    {
        $this->posix->allows('isPosixAvailable')
            ->andReturnFalse();
        $this->statResolver->allows()
            ->stat('/path/to/my_file', true, true)
            ->andReturn([
                'gid' => 123,
                'mode' => 0000,
                'uid' => 456,
            ]);
        $this->accessResolver->allows()
            ->isExecutable('/path/to/my_file')
            ->andReturnTrue();

        static::assertEquals(
            [
                'gid' => 123,
                'mode' => 0001,
                'uid' => 456,
            ],
            $this->resolver->stat('/path/to/my_file', link: true, quiet: true)
        );
    }

    public function testStatTweaksWorldPermissionBitsWhenReadableWithoutPosixAvailable(): void
    {
        $this->posix->allows('isPosixAvailable')
            ->andReturnFalse();
        $this->statResolver->allows()
            ->stat('/path/to/my_file', true, true)
            ->andReturn([
                'gid' => 123,
                'mode' => 0000,
                'uid' => 456,
            ]);
        $this->accessResolver->allows()
            ->isReadable('/path/to/my_file')
            ->andReturnTrue();

        static::assertEquals(
            [
                'gid' => 123,
                'mode' => 0004,
                'uid' => 456,
            ],
            $this->resolver->stat('/path/to/my_file', link: true, quiet: true)
        );
    }

    public function testStatTweaksWorldPermissionBitsWhenWritableWithoutPosixAvailable(): void
    {
        $this->posix->allows('isPosixAvailable')
            ->andReturnFalse();
        $this->statResolver->allows()
            ->stat('/path/to/my_file', true, true)
            ->andReturn([
                'gid' => 123,
                'mode' => 0444,
                'uid' => 456,
            ]);
        $this->accessResolver->allows()
            ->isWritable('/path/to/my_file')
            ->andReturnTrue();

        static::assertEquals(
            [
                'gid' => 123,
                'mode' => 0446,
                'uid' => 456,
            ],
            $this->resolver->stat('/path/to/my_file', link: true, quiet: true)
        );
    }
}
