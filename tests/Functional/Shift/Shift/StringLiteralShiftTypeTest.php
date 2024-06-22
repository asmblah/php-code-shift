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

namespace Asmblah\PhpCodeShift\Tests\Functional\Shift\Shift;

use Asmblah\PhpCodeShift\CodeShift;
use Asmblah\PhpCodeShift\Shifter\Filter\FileFilter;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\String\StringLiteralShiftSpec;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;

/**
 * Class StringLiteralShiftTypeTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StringLiteralShiftTypeTest extends AbstractTestCase
{
    private CodeShift $codeShift;

    public function setUp(): void
    {
        $this->codeShift = new CodeShift();

        $this->codeShift->shift(
            new StringLiteralShiftSpec(
                'mystring',
                'yourstring'
            ),
            new FileFilter(dirname(__DIR__, 2) . '/Fixtures/**')
        );
    }

    public function tearDown(): void
    {
        $this->codeShift->uninstall();
    }

    public function testCanReplaceArbitraryStrings(): void
    {
        $result = include __DIR__ . '/../../Fixtures/string_shift_test.php';

        static::assertSame('this is yourstring here and that is also yourstring there', $result);
    }
}
