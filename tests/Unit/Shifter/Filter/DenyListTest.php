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
    private ?DenyList $denyList;

    public function setUp(): void
    {
        $this->denyList = new DenyList();
    }

    public function testFileMatchesReturnsFalseWhenThereAreNoFiltersInTheList(): void
    {
        static::assertFalse($this->denyList->fileMatches('/my/path.php'));
    }

    public function testFileMatchesReturnsFalseWhenThereAreNoMatchingFiltersInTheList(): void
    {
        $filter = mock(FileFilterInterface::class);
        $filter->allows()
            ->fileMatches('/my/path.php')
            ->andReturn(false);
        $this->denyList->addFilter($filter);

        static::assertFalse($this->denyList->fileMatches('/my/path.php'));
    }

    public function testFileMatchesReturnsTrueWhenThereIsAMatchingFilterInTheList(): void
    {
        $filter = mock(FileFilterInterface::class);
        $filter->allows()
            ->fileMatches('/my/path.php')
            ->andReturn(true);
        $this->denyList->addFilter($filter);

        static::assertTrue($this->denyList->fileMatches('/my/path.php'));
    }
}
