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
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\FunctionHook\FunctionHookShiftSpec;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\FunctionHook\FunctionHookShiftType;
use Asmblah\PhpCodeShift\Shifter\Shift\Shifter\ShiftSetShifterInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\ShiftSet;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Generator;
use Mockery\MockInterface;

/**
 * Class FunctionHookShiftTypeTest.
 *
 * Tests the code transpilation logic of FunctionHookShiftType.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class FunctionHookShiftTypeTest extends AbstractTestCase
{
    private DelegatingShift $delegatingShift;
    private MockInterface&DenyListInterface $denyList;
    private MockInterface&FileFilterInterface $fileFilter;
    private ShiftSet $shiftSet;
    private ShiftSetShifterInterface $shiftSetShifter;

    public function setUp(): void
    {
        $this->delegatingShift = new DelegatingShift();
        $this->delegatingShift->registerShiftType(new FunctionHookShiftType());
        $this->denyList = mock(DenyListInterface::class);
        $this->fileFilter = mock(FileFilterInterface::class);
        $this->shiftSetShifter = Shared::getStreamShifter()->getShiftSetShifter();

        $this->shiftSet = new ShiftSet('/my/path/to/my_module.php', [
            new Shift(
                $this->delegatingShift,
                new FunctionHookShiftSpec(
                    'myFunc',
                    function () {}
                ),
                $this->denyList,
                $this->fileFilter
            ),
        ]);
    }

    /**
     * @dataProvider dataProviderFunctionHooking
     */
    public function testFunctionHookingGeneratesCorrectCode(
        string $input,
        string $expectedOutput
    ): void {
        static::assertSame($expectedOutput, $this->shiftSetShifter->shift($input, $this->shiftSet));
    }

    public static function dataProviderFunctionHooking(): Generator
    {
        yield 'simple return with matching function call is hooked' => [
            '<?php return myFunc();',
            '<?php return \Asmblah\PhpCodeShift\Shifter\Hook\Invoker::myFunc();',
        ];

        yield 'simple return with non-matching function call is not hooked' => [
            '<?php return yourFunc();',
            '<?php return yourFunc();',
        ];

        yield 'multiple calls may be replaced across different lines, preserving line numbers' => [
            <<<EOS
            <?php
            return myFunc(1, 2) .
                   'and also ' .
                   myFunc(3, 4);
            EOS,

            <<<EOS
            <?php
            return \Asmblah\PhpCodeShift\Shifter\Hook\Invoker::myFunc(1, 2) .
                   'and also ' .
                   \Asmblah\PhpCodeShift\Shifter\Hook\Invoker::myFunc(3, 4);
            EOS,
        ];

        yield 'call arguments split different lines have line numbers preserved' => [
            <<<EOS
            <?php
            return myFunc(
                       1,
                       2
                   ) .
                   'and also ' .
                   myFunc(3, 4);
            EOS,

            // Note indentation is _not_ preserved.
            <<<EOS
            <?php
            return \Asmblah\PhpCodeShift\Shifter\Hook\Invoker::myFunc(
            1, 
            2)
             .
                   'and also ' .
                   \Asmblah\PhpCodeShift\Shifter\Hook\Invoker::myFunc(3, 4);
            EOS,
        ];
    }
}
