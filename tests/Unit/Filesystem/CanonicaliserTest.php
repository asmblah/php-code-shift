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

namespace Asmblah\PhpCodeShift\Tests\Unit\Filesystem;

use Asmblah\PhpCodeShift\Environment\EnvironmentInterface;
use Asmblah\PhpCodeShift\Filesystem\Canonicaliser;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Generator;
use Mockery\MockInterface;

/**
 * Class CanonicaliserTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class CanonicaliserTest extends AbstractTestCase
{
    private Canonicaliser $canonicaliser;
    private MockInterface&EnvironmentInterface $environment;

    public function setUp(): void
    {
        $this->environment = mock(EnvironmentInterface::class);

        $this->canonicaliser = new Canonicaliser($this->environment);
    }

    /**
     * @dataProvider canonicaliseDataProvider
     */
    public function testCanonicaliseReturnsACanonicalPathUnchanged(
        string $path,
        string $expectedResult,
        string $cwd
    ): void {
        static::assertSame($expectedResult, $this->canonicaliser->canonicalise($path, $cwd));
    }

    public static function canonicaliseDataProvider(): Generator
    {
        yield 'already canonical' => [
            '/my/canonical/path',
            '/my/canonical/path',
            '/home/me',
        ];

        yield 'empty string' => [
            '',
            '',
            '/home/me',
        ];

        yield 'contains empty path segments' => [
            '/my/path/with/some///empty//directory/symbols',
            '/my/path/with/some/empty/directory/symbols',
            '/home/me',
        ];

        yield 'contains same-directory symbols' => [
            '/my/path/./with/some/././same-directory/symbols',
            '/my/path/with/some/same-directory/symbols',
            '/home/me',
        ];

        yield 'contains parent-directory symbols' => [
            '/my/path/here/../with/some/../../parent-directory/symbols',
            '/my/path/parent-directory/symbols',
            '/home/me',
        ];

        yield 'contains dotfiles' => [
            '/my/path/.that/has/.dotfiles/.in',
            '/my/path/.that/has/.dotfiles/.in', // Should be left unchanged.
            '/home/me',
        ];

        yield 'relative to current directory' => [
            './my/sub/path',
            '/home/me/my/sub/path',
            '/home/me',
        ];

        yield 'relative to current directory when cwd has trailing slash' => [
            './my/sub/path',
            '/home/me/my/sub/path',
            '/home/me/',
        ];

        yield 'relative to parent directory' => [
            '../my/sub/path',
            '/home/my/sub/path',
            '/home/you',
        ];

        yield 'relative to parent directory when cwd has trailing slash' => [
            '../my/sub/path',
            '/home/my/sub/path',
            '/home/you/',
        ];
    }

    public function testCanonicaliseFetchesCwdFromEnvironmentWhenNotSpecified(): void
    {
        $this->environment->allows()
            ->getCwd()
            ->andReturn('/my/canonical');

        static::assertSame(
            '/my/canonical/path/to/stuff.txt',
            $this->canonicaliser->canonicalise('./path/to/stuff.txt')
        );
    }
}
