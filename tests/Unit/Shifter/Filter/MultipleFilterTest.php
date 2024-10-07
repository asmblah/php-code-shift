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
use Asmblah\PhpCodeShift\Shifter\Filter\FileFilterInterface;
use Asmblah\PhpCodeShift\Shifter\Filter\MultipleFilter;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Generator;

/**
 * Class MultipleFilterTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class MultipleFilterTest extends AbstractTestCase
{
    /**
     * @dataProvider fileMatchesProvider
     * @param FileFilterInterface[] $subFilters
     * @param string $path
     * @param bool $expectedResult
     */
    public function testFileMatchesReturnsCorrectResult(
        array $subFilters,
        string $path,
        bool $expectedResult
    ): void {
        $filter = new MultipleFilter($subFilters);

        static::assertSame($expectedResult, $filter->fileMatches($path));
    }

    public static function fileMatchesProvider(): Generator
    {
        yield 'single sub-filter, matching implicit file protocol with star' => [
            [new FileFilter('/my/path/to*.txt')],
            '/my/path/to_my_file.txt',
            true,
        ];

        yield 'single sub-filter, non-matching implicit file protocol with star' => [
            [new FileFilter('/my/path/to*.txt')],
            '/my/path/to/my_file.txt',
            false,
        ];

        yield 'single sub-filter, matching explicit Phar protocol' => [
            [new FileFilter('/my/path/*/it.txt')],
            'phar:///my/path/to/it.txt',
            true,
        ];

        yield 'single sub-filter, non-matching explicit Phar protocol' => [
            [new FileFilter('/my/path/*/it.txt')],
            'phar:///my/path/to/not_it.txt',
            false,
        ];

        yield 'two sub-filters, only first one matches' => [
            [new FileFilter('/my/path/to*.txt'), new FileFilter('/your/path/here')],
            '/my/path/to_my_file.txt',
            true,
        ];

        yield 'two sub-filters, only second one matches' => [
            [new FileFilter('/your/path/here'), new FileFilter('/my/path/to*.txt')],
            '/my/path/to_my_file.txt',
            true,
        ];

        yield 'two sub-filters, neither matches' => [
            [new FileFilter('/my/path/to/somewhere_else.txt'), new FileFilter('/your/path/here')],
            '/my/path/to_my_file.txt',
            false,
        ];
    }

    public function testGetRegexPartFetchesTheRawRegexPartWithOneSubFilter(): void
    {
        $filter = new MultipleFilter([new FileFilter('my/**/patt*ern')]);

        static::assertSame(
            'my/[\s\S]*?/patt[^/]*?ern|file://my/[\s\S]*?/patt[^/]*?ern|phar://my/[\s\S]*?/patt[^/]*?ern',
            $filter->getRegexPart()
        );
    }

    public function testGetRegexPartFetchesTheRawRegexPartWithTwoSubFilters(): void
    {
        $filter = new MultipleFilter([
            new FileFilter('my/**/patt*ern'),
            new FileFilter('your/**/patt*ern'),
        ]);

        static::assertSame(
            'my/[\s\S]*?/patt[^/]*?ern|file://my/[\s\S]*?/patt[^/]*?ern|phar://my/[\s\S]*?/patt[^/]*?ern|' .
            'your/[\s\S]*?/patt[^/]*?ern|file://your/[\s\S]*?/patt[^/]*?ern|phar://your/[\s\S]*?/patt[^/]*?ern',
            $filter->getRegexPart()
        );
    }

    public function testGetSubFiltersFetchesThem(): void
    {
        $subFilter1 = mock(FileFilterInterface::class, [
            'getRegexPart' => '(?:abc)',
        ]);
        $subFilter2 = mock(FileFilterInterface::class, [
            'getRegexPart' => '(?:def)',
        ]);

        $subFilters = (new MultipleFilter([$subFilter1, $subFilter2]))->getSubFilters();

        static::assertSame([$subFilter1, $subFilter2], $subFilters);
    }
}
