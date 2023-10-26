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

use Asmblah\PhpCodeShift\Shifter\Filter\FileFilterInterface;
use Asmblah\PhpCodeShift\Shifter\Filter\MultipleFilter;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery\MockInterface;

/**
 * Class MultipleFilterTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class MultipleFilterTest extends AbstractTestCase
{
    private MultipleFilter $filter;
    private MockInterface&FileFilterInterface $subFilter1;
    private MockInterface&FileFilterInterface $subFilter2;

    public function setUp(): void
    {
        $this->subFilter1 = mock(FileFilterInterface::class, [
            'fileMatches' => false,
        ]);
        $this->subFilter2 = mock(FileFilterInterface::class, [
            'fileMatches' => false,
        ]);

        $this->filter = new MultipleFilter([$this->subFilter1, $this->subFilter2]);
    }

    public function testFileMatchesReturnsTrueWhenFirstSubFilterMatches(): void
    {
        $this->subFilter1->allows()
            ->fileMatches('/my/path/to/my_module.php')
            ->andReturnTrue();

        static::assertTrue($this->filter->fileMatches('/my/path/to/my_module.php'));
    }

    public function testFileMatchesReturnsTrueWhenSecondSubFilterMatches(): void
    {
        $this->subFilter2->allows()
            ->fileMatches('/my/path/to/my_module.php')
            ->andReturnTrue();

        static::assertTrue($this->filter->fileMatches('/my/path/to/my_module.php'));
    }

    public function testFileMatchesReturnsFalseWhenNoSubFilterMatches(): void
    {
        static::assertFalse($this->filter->fileMatches('/my/path/to/some_module.php'));
    }
}
