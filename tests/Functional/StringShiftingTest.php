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

namespace Asmblah\PhpCodeShift\Tests\Functional;

use Asmblah\PhpCodeShift\CodeShift;
use Asmblah\PhpCodeShift\Shifter\Filter\FileFilter;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\String\StringShiftSpec;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;

/**
 * Class StringShiftingTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StringShiftingTest extends AbstractTestCase
{
    private ?CodeShift $codeShift;

    public function setUp(): void
    {
        $this->codeShift = new CodeShift();

        $this->codeShift->shift(
            new StringShiftSpec(
                'mystring',
                'yourstring'
            ),
            new FileFilter(__DIR__ . '/Fixtures/**')
        );
    }

    public function tearDown(): void
    {
        $this->codeShift->uninstall();
    }

    public function testCanReplaceArbitraryStrings(): void
    {
        $result = include __DIR__ . '/Fixtures/string_shift_test.php';

        static::assertSame('this is yourstring here and that is also yourstring there', $result);
    }
}
