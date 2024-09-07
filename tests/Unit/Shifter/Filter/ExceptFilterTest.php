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
use Mockery\MockInterface;

/**
 * Class ExceptFilterTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ExceptFilterTest extends AbstractTestCase
{
    private MockInterface&FileFilterInterface $exceptSubFilter;
    private ExceptFilter $filter;
    private MockInterface&FileFilterInterface $onlySubFilter;

    public function setUp(): void
    {
        $this->exceptSubFilter = mock(FileFilterInterface::class);
        $this->onlySubFilter = mock(FileFilterInterface::class);

        $this->filter = new ExceptFilter(
            $this->exceptSubFilter,
            $this->onlySubFilter
        );
    }

    public function testFileMatchesReturnsTrueWhenExceptSubFilterReturnsFalseAndOnlySubFilterReturnsTrue(): void
    {
        $this->exceptSubFilter->allows()
            ->fileMatches('/my/path/to/my_file.txt')
            ->andReturnFalse();
        $this->onlySubFilter->allows()
            ->fileMatches('/my/path/to/my_file.txt')
            ->andReturnTrue();

        static::assertTrue($this->filter->fileMatches('/my/path/to/my_file.txt'));
    }

    public function testFileMatchesReturnsFalseWhenExceptSubFilterReturnsTrueAndOnlySubFilterReturnsTrue(): void
    {
        $this->exceptSubFilter->allows()
            ->fileMatches('/my/path/to/my_file.txt')
            ->andReturnTrue();
        $this->onlySubFilter->allows()
            ->fileMatches('/my/path/to/my_file.txt')
            ->andReturnTrue();

        static::assertFalse($this->filter->fileMatches('/my/path/to/my_file.txt'));
    }

    public function testFileMatchesReturnsFalseWhenExceptSubFilterReturnsTrueAndOnlySubFilterReturnsFalse(): void
    {
        $this->exceptSubFilter->allows()
            ->fileMatches('/my/path/to/my_file.txt')
            ->andReturnTrue();
        $this->onlySubFilter->allows()
            ->fileMatches('/my/path/to/my_file.txt')
            ->andReturnFalse();

        static::assertFalse($this->filter->fileMatches('/my/path/to/my_file.txt'));
    }

    public function testFileMatchesReturnsFalseWhenExceptSubFilterReturnsFalseAndOnlySubFilterReturnsFalse(): void
    {
        $this->exceptSubFilter->allows()
            ->fileMatches('/my/path/to/my_file.txt')
            ->andReturnFalse();
        $this->onlySubFilter->allows()
            ->fileMatches('/my/path/to/my_file.txt')
            ->andReturnFalse();

        static::assertFalse($this->filter->fileMatches('/my/path/to/my_file.txt'));
    }
}
