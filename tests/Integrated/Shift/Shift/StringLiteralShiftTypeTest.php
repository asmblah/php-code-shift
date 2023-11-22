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

namespace Asmblah\PhpCodeShift\Tests\Integrated\Shift\Shift;

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
use Generator;
use Mockery\MockInterface;

/**
 * Class StringLiteralShiftTypeTest.
 *
 * Tests the code transpilation logic of StringLiteralShiftType.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StringLiteralShiftTypeTest extends AbstractTestCase
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

    /**
     * @dataProvider dataProviderStringReplacement
     */
    public function testArbitraryStringReplacementGeneratesCorrectCode(
        string $input,
        string $expectedOutput
    ): void {
        static::assertSame($expectedOutput, $this->shiftSetShifter->shift($input, $this->shiftSet));
    }

    public static function dataProviderStringReplacement(): Generator
    {
        yield 'simple return with plain string literal' => [
            '<?php return "this is mystring here";',
            '<?php return \'this is yourstring here\';',
        ];

        yield 'return with string literal containing interpolation' => [
            '<?php return "this $here is mystring you see";',
            '<?php return "this $here is yourstring you see";',
        ];

        yield 'matching identifiers are left unchanged' => [
            '<?php const mystring = "hello"; return "it is: " . mystring;',
            '<?php const mystring = "hello"; return "it is: " . mystring;',
        ];

        yield 'multiple literals may be replaced across different lines, preserving line numbers' => [
            <<<EOS
            <?php
            return 'this is mystring over here ' .
                   'and also ' .
                   'this is mystring over there.';
            EOS,

            <<<EOS
            <?php
            return 'this is yourstring over here ' .
                   'and also ' .
                   'this is yourstring over there.';
            EOS,
        ];
    }
}
