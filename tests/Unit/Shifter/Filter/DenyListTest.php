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

use Asmblah\PhpCodeShift\Shifter\Filter\DenyList;
use Asmblah\PhpCodeShift\Shifter\Filter\FileFilterInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;

/**
 * Class DenyListTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class DenyListTest extends AbstractTestCase
{
    private DenyList $denyList;

    public function setUp(): void
    {
        $this->denyList = new DenyList();
    }

    public function testClearRemovesAllFilters(): void
    {
        $filter = mock(FileFilterInterface::class, [
            'getRegexPart' => '\/my\/path\.php',
        ]);
        $this->denyList->addFilter($filter);

        $this->denyList->clear();

        // Would have been allowed had we not cleared the list just above.
        static::assertFalse($this->denyList->fileMatches('/my/path.php'));
        static::assertSame('(?!)', $this->denyList->getRegexPart());
    }

    public function testFileMatchesReturnsFalseWhenThereAreNoFiltersInTheList(): void
    {
        static::assertFalse($this->denyList->fileMatches('/my/path.php'));
    }

    public function testFileMatchesReturnsFalseWhenThereAreNoMatchingFiltersInTheList(): void
    {
        $filter = mock(FileFilterInterface::class, [
            'getRegexPart' => '\/your\/path\.php',
        ]);
        $this->denyList->addFilter($filter);

        static::assertFalse($this->denyList->fileMatches('/my/path.php'));
    }

    public function testFileMatchesReturnsTrueWhenThereIsAMatchingFilterInTheList(): void
    {
        $filter = mock(FileFilterInterface::class, [
            'getRegexPart' => '\/my\/path\.php',
        ]);
        $this->denyList->addFilter($filter);

        static::assertTrue($this->denyList->fileMatches('/my/path.php'));
    }

    public function testGetFiltersFetchesAddedFilters(): void
    {
        $filter1 = mock(FileFilterInterface::class, [
            'getRegexPart' => '(?:abc)',
        ]);
        $filter2 = mock(FileFilterInterface::class, [
            'getRegexPart' => '(?:def)',
        ]);
        $this->denyList->addFilter($filter1);
        $this->denyList->addFilter($filter2);

        static::assertSame([$filter1, $filter2], $this->denyList->getFilters());
    }

    public function testGetRegexPartReturnsEmptyNegativeLookaheadToForceFailureInitially(): void
    {
        static::assertSame('(?!)', $this->denyList->getRegexPart());
    }

    public function testGetRegexPartReturnsCorrectPatternWithMultipleFilters(): void
    {
        $filter1 = mock(FileFilterInterface::class, [
            'getRegexPart' => '\/my\/first\.php',
        ]);
        $this->denyList->addFilter($filter1);
        $filter2 = mock(FileFilterInterface::class, [
            'getRegexPart' => '\/my\/second\.php',
        ]);
        $this->denyList->addFilter($filter2);

        static::assertSame('\/my\/first\.php|\/my\/second\.php', $this->denyList->getRegexPart());
    }
}
