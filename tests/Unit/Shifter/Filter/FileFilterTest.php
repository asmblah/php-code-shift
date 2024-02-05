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

use Asmblah\PhpCodeShift\Shifter\Filter\FileFilter;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;

/**
 * Class FileFilterTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class FileFilterTest extends AbstractTestCase
{
    private FileFilter $filter;

    public function setUp(): void
    {
        $this->filter = new FileFilter(escapeshellcmd(__DIR__) . '/*.php');
    }

    public function testFileMatchesReturnsTrueForMatchingPathOfImplicitFileProtocol(): void
    {
        static::assertTrue($this->filter->fileMatches(__FILE__));
    }

    public function testFileMatchesReturnsTrueForMatchingPathOfExplicitFileProtocol(): void
    {
        static::assertTrue($this->filter->fileMatches('file://' . __FILE__));
    }

    public function testFileMatchesReturnsTrueForMatchingPathOfPharProtocol(): void
    {
        static::assertTrue($this->filter->fileMatches('phar://' . __FILE__));
    }

    public function testFileMatchesReturnsFalseForNonMatchingPathOfImplicitFileProtocol(): void
    {
        static::assertFalse($this->filter->fileMatches(__DIR__));
    }

    public function testFileMatchesReturnsFalseForNonMatchingPathOfExplicitFileProtocol(): void
    {
        static::assertFalse($this->filter->fileMatches('file://' . __DIR__));
    }

    public function testFileMatchesReturnsFalseForNonMatchingPathOfPharProtocol(): void
    {
        static::assertFalse($this->filter->fileMatches('phar://' . __DIR__));
    }

    public function testGetPatternsFetchesAllPatterns(): void
    {
        $filter = new FileFilter('my/pattern');

        static::assertEquals(
            [
                'file://my/pattern',
                'phar://my/pattern',
                'my/pattern',
            ],
            $filter->getPatterns()
        );
    }
}
