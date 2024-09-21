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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter\Filter;

use Asmblah\PhpCodeShift\Shifter\Filter\FileFilter;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Generator;

/**
 * Class FileFilterTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class FileFilterTest extends AbstractTestCase
{
    /**
     * @dataProvider fileMatchesProvider
     * @param string $pattern
     * @param string $path
     * @param bool $expectedResult
     */
    public function testFileMatchesReturnsCorrectResult(
        string $pattern,
        string $path,
        bool $expectedResult
    ): void {
        $filter = new FileFilter($pattern);

        static::assertSame($expectedResult, $filter->fileMatches($path));
    }

    public static function fileMatchesProvider(): Generator
    {
        yield 'matching implicit file protocol with star' => [
            '/my/path/to*.txt',
            '/my/path/to_my_file.txt',
            true,
        ];

        yield 'non-matching implicit file protocol with star' => [
            '/my/path/to*.txt',
            '/my/path/to/my_file.txt',
            false,
        ];

        yield 'matching implicit file protocol with star and directory' => [
            '/my/path/to/*/my_file.txt',
            '/my/path/to/the/my_file.txt',
            true,
        ];

        yield 'non-matching implicit file protocol with star and subdirectory' => [
            '/my/path/to/*.txt',
            '/my/path/to/the/my_file.txt',
            false,
        ];

        yield 'matching implicit file protocol with globstar' => [
            '/my/path/to/**/it.txt',
            '/my/path/to/somewhere/and/then/it.txt',
            true,
        ];

        yield 'non-matching implicit file protocol with globstar' => [
            '/my/path/to/**/it.txt',
            '/my/path/to/somewhere/and/then/not_it.txt',
            false,
        ];

        yield 'matching implicit file protocol with globstar only, matches anything' => [
            '**',
            '/my/path/to/somewhere/and/then/it.txt',
            true,
        ];

        yield 'matching implicit file protocol with globstar-/ when directory present' => [
            '/my/path/to/**/it.txt',
            '/my/path/to/erm/it.txt',
            true,
        ];

        yield 'non-matching implicit file protocol with globstar-/ when missing directory' => [
            '/my/path/to/**/it.txt',
            '/my/path/to/it.txt',
            false,
        ];

        yield 'matching implicit file protocol with leading globstar' => [
            '**/it.txt',
            '/my/path/to/it.txt',
            true,
        ];

        yield 'non-matching implicit file protocol with leading globstar' => [
            '**/it.txt',
            '/my/path/to/not_it.txt',
            false,
        ];

        yield 'non-matching implicit file protocol with partial match at start' => [
            '/path/to/my_file.txt',
            '/my/path/to/my_file.txt',
            false,
        ];

        yield 'non-matching implicit file protocol with partial match at end' => [
            '/my/path/to',
            '/my/path/to/my_file.txt',
            false,
        ];

        yield 'matching implicit file protocol with trailing slashes on pattern and path' => [
            '/my/dir/',
            '/my/dir/',
            true,
        ];

        yield 'matching implicit file protocol with trailing slash only on pattern' => [
            '/my/dir/',
            '/my/dir',
            true,
        ];

        yield 'matching implicit file protocol with trailing slash only on path' => [
            '/my/dir',
            '/my/dir/',
            true,
        ];

        yield 'matching implicit file protocol with trailing slashes on neither pattern nor path' => [
            '/my/dir',
            '/my/dir',
            true,
        ];

        yield 'matching explicit file protocol' => [
            '/my/path/*/it.txt',
            'file:///my/path/to/it.txt',
            true,
        ];

        yield 'non-matching explicit file protocol' => [
            '/my/path/*/it.txt',
            'file:///my/path/to/not_it.txt',
            false,
        ];

        yield 'matching explicit Phar protocol' => [
            '/my/path/*/it.txt',
            'phar:///my/path/to/it.txt',
            true,
        ];

        yield 'non-matching explicit Phar protocol' => [
            '/my/path/*/it.txt',
            'phar:///my/path/to/not_it.txt',
            false,
        ];
    }

    public function testGetPatternFetchesThePattern(): void
    {
        $filter = new FileFilter('my/pattern');

        static::assertSame('my/pattern', $filter->getPattern());
    }

    public function testGetRegexFetchesTheRegexPattern(): void
    {
        $filter = new FileFilter('my/**/patt*ern');

        static::assertSame(
            '#\A(?:my/[\s\S]*?/patt[^/]*?ern|file://my/[\s\S]*?/patt[^/]*?ern|phar://my/[\s\S]*?/patt[^/]*?ern)\Z#',
            $filter->getRegex()
        );
    }
}
