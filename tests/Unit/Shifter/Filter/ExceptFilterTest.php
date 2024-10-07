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

use Asmblah\PhpCodeShift\Shifter\Filter\ExceptFilter;
use Asmblah\PhpCodeShift\Shifter\Filter\FileFilterInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;

/**
 * Class ExceptFilterTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ExceptFilterTest extends AbstractTestCase
{
    public function testFileMatchesReturnsTrueWhenExceptSubFilterDoesNotMatchAndOnlySubFilterMatches(): void
    {
        $filter = new ExceptFilter(
            exceptFilter: mock(FileFilterInterface::class, [
                'getRegexPart' => '\/not\/in\/here\/[\s\S]*?',
            ]),
            onlyFilter: mock(FileFilterInterface::class, [
                'getRegexPart' => '\/my\/path\/to\/my_file\.txt',
            ])
        );

        static::assertTrue($filter->fileMatches('/my/path/to/my_file.txt'));
    }

    public function testFileMatchesReturnsFalseWhenBothExceptSubFilterMatchesAndOnlySubFilterMatches(): void
    {
        $filter = new ExceptFilter(
            exceptFilter: mock(FileFilterInterface::class, [
                'getRegexPart' => '\/my\/path\/to\/my_file\.txt',
            ]),
            onlyFilter: mock(FileFilterInterface::class, [
                'getRegexPart' => '\/my\/path\/to\/my_file\.txt',
            ])
        );

        static::assertFalse($filter->fileMatches('/my/path/to/my_file.txt'));
    }

    public function testFileMatchesReturnsFalseWhenExceptSubFilterMatchesAndOnlySubFilterDoesNotMatch(): void
    {
        $filter = new ExceptFilter(
            exceptFilter: mock(FileFilterInterface::class, [
                'getRegexPart' => '\/my\/path\/to\/my_file\.txt',
            ]),
            onlyFilter: mock(FileFilterInterface::class, [
                'getRegexPart' => '\/not\/my\/path\/to\/my_file\.txt',
            ])
        );

        static::assertFalse($filter->fileMatches('/my/path/to/my_file.txt'));
    }

    public function testFileMatchesReturnsFalseWhenExceptSubFilterReturnsFalseAndOnlySubFilterReturnsFalse(): void
    {
        $filter = new ExceptFilter(
            exceptFilter: mock(FileFilterInterface::class, [
                'getRegexPart' => '\/not\/my\/path\/to\/my_file\.txt',
            ]),
            onlyFilter: mock(FileFilterInterface::class, [
                'getRegexPart' => '\/not\/my\/path\/to\/my_file\.txt',
            ])
        );

        static::assertFalse($filter->fileMatches('/my/path/to/my_file.txt'));
    }

    public function testGetExceptFilterFetchesTheExceptFilter(): void
    {
        $exceptFilter = mock(FileFilterInterface::class, [
            'getRegexPart' => '\/not\/in\/here\/[\s\S]*?',
        ]);
        $filter = new ExceptFilter(
            exceptFilter: $exceptFilter,
            onlyFilter: mock(FileFilterInterface::class, [
                'getRegexPart' => '\/my\/path\/to\/my_file\.txt',
            ])
        );

        static::assertSame($exceptFilter, $filter->getExceptFilter());
    }

    public function testGetOnlyFilterFetchesTheOnlyFilter(): void
    {
        $onlyFilter = mock(FileFilterInterface::class, [
            'getRegexPart' => '\/my\/path\/to\/my_file\.txt',
        ]);
        $filter = new ExceptFilter(
            exceptFilter: mock(FileFilterInterface::class, [
                'getRegexPart' => '\/not\/in\/here\/[\s\S]*?',
            ]),
            onlyFilter: $onlyFilter
        );

        static::assertSame($onlyFilter, $filter->getOnlyFilter());
    }
}
