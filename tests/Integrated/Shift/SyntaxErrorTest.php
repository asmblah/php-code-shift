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

namespace Asmblah\PhpCodeShift\Tests\Integrated\Shift;

use Asmblah\PhpCodeShift\Exception\ParseFailedException;
use Asmblah\PhpCodeShift\Shared;
use Asmblah\PhpCodeShift\Shifter\Filter\DenyListInterface;
use Asmblah\PhpCodeShift\Shifter\Filter\FileFilterInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\DelegatingShift;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\String\StringLiteralShiftSpec;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\String\StringLiteralShiftType;
use Asmblah\PhpCodeShift\Shifter\Shift\Shifter\ShiftSetShifterInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\ShiftSet;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery\MockInterface;

/**
 * Class SyntaxErrorTest.
 *
 * Tests the handling of syntax errors while parsing code modules to shift.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class SyntaxErrorTest extends AbstractTestCase
{
    private DelegatingShift $delegatingShift;
    private MockInterface&DenyListInterface $denyList;
    private MockInterface&FileFilterInterface $fileFilter;
    private ShiftSet $shiftSet;
    private ShiftSetShifterInterface $shiftSetShifter;

    public function setUp(): void
    {
        $this->delegatingShift = new DelegatingShift();
        $this->delegatingShift->registerShiftType(new StringLiteralShiftType());
        $this->denyList = mock(DenyListInterface::class);
        $this->fileFilter = mock(FileFilterInterface::class);
        $this->shiftSetShifter = Shared::getStreamShifter()->getShiftSetShifter();

        $this->shiftSet = new ShiftSet('/my/path/to/my_module.php', [
            new Shift(
                $this->delegatingShift,
                new StringLiteralShiftSpec(
                    'mystring',
                    'yourstring'
                ),
                $this->denyList,
                $this->fileFilter
            ),
        ]);
    }

    public function testRaisesExceptionWhenContentsHaveSyntaxError(): void
    {
        $input = <<<PHP
<?php

return I am a syntax error;
PHP;

        $this->expectException(ParseFailedException::class);
        $this->expectExceptionMessage(
            'Failed to parse path "/my/path/to/my_module.php" :: PhpParser\Error ' .
            '"Syntax error, unexpected T_STRING, expecting \';\' on line 3"',
        );

        $this->shiftSetShifter->shift($input, $this->shiftSet);
    }
}
