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

namespace Asmblah\PhpCodeShift\Tests\Integrated\Shift\Shift;

use Asmblah\PhpCodeShift\Shared;
use Asmblah\PhpCodeShift\Shifter\Filter\DenyListInterface;
use Asmblah\PhpCodeShift\Shifter\Filter\FileFilterInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\ClassHook\ClassHookShiftSpec;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\ClassHook\ClassHookShiftType;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\DelegatingShift;
use Asmblah\PhpCodeShift\Shifter\Shift\Shifter\ShiftSetShifterInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\ShiftSet;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Generator;
use Mockery\MockInterface;

/**
 * Class ClassHookShiftTypeTest.
 *
 * Tests the code transpilation logic of ClassHookShiftType.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ClassHookShiftTypeTest extends AbstractTestCase
{
    private DelegatingShift $delegatingShift;
    private MockInterface&DenyListInterface $denyList;
    private MockInterface&FileFilterInterface $fileFilter;
    private ShiftSet $shiftSet;
    private ShiftSetShifterInterface $shiftSetShifter;

    public function setUp(): void
    {
        $this->delegatingShift = new DelegatingShift();
        $this->delegatingShift->registerShiftType(new ClassHookShiftType());
        $this->denyList = mock(DenyListInterface::class);
        $this->fileFilter = mock(FileFilterInterface::class);
        $this->shiftSetShifter = Shared::getStreamShifter()->getShiftSetShifter();

        /** @var class-string $className */
        $className = 'My\Stuff\MyClass';
        /** @var class-string $replacementClassName */
        $replacementClassName = 'Your\Stuff\YourClass';

        $this->shiftSet = new ShiftSet('/my/path/to/my_module.php', [
            new Shift(
                $this->delegatingShift,
                new ClassHookShiftSpec(
                    $className,
                    $replacementClassName
                ),
                $this->denyList,
                $this->fileFilter
            ),
        ]);
    }

    /**
     * @dataProvider dataProviderClassHooking
     */
    public function testClassHookingGeneratesCorrectCode(
        string $input,
        string $expectedOutput
    ): void {
        static::assertSame($expectedOutput, $this->shiftSetShifter->shift($input, $this->shiftSet));
    }

    public static function dataProviderClassHooking(): Generator
    {
        yield 'simple return with matching `new` instantiation is hooked' => [
            '<?php return new \My\Stuff\MyClass(1001);',
            '<?php return new (\Asmblah\PhpCodeShift\Shifter\Hook\ClassHooks::getReplacement(\'My\\\Stuff\\\MyClass\'))(1001);',
        ];

        yield 'simple return with non-matching `new` instantiation is not hooked' => [
            '<?php return new \My\Stuff\MyOtherClass();',
            '<?php return new \My\Stuff\MyOtherClass();',
        ];

        yield 'function call to function with identical name is not hooked' => [
            '<?php return \My\Stuff\MyClass();',
            '<?php return \My\Stuff\MyClass();',
        ];

        yield 'simple return with matching static method call is hooked' => [
            '<?php return \My\Stuff\MyClass::myStaticMethod(101);',
            '<?php return (\Asmblah\PhpCodeShift\Shifter\Hook\ClassHooks::getReplacement(\'My\\\Stuff\\\MyClass\'))::myStaticMethod(101);',
        ];

        yield 'simple return with matching class constant lookup is hooked' => [
            '<?php return \My\Stuff\MyClass::MY_CONST;',
            '<?php return (\Asmblah\PhpCodeShift\Shifter\Hook\ClassHooks::getReplacement(\'My\\\Stuff\\\MyClass\'))::MY_CONST;',
        ];

        yield 'simple return with matching variable class constant lookup is hooked' => [
            '<?php return \My\Stuff\MyClass::{$myPrefix . $mySuffix};',
            '<?php return (\Asmblah\PhpCodeShift\Shifter\Hook\ClassHooks::getReplacement(\'My\\\Stuff\\\MyClass\'))::{$myPrefix . $mySuffix};',
        ];

        yield 'multiple references may be replaced across different lines, preserving line numbers' => [
            <<<EOS
            <?php
            return (new \My\Stuff\MyClass)->myInstanceMethod() .
                   ' and also ' .
                   \My\Stuff\MyClass::myStaticMethod() .
                   ' and then ' .
                   \My\Stuff\MyClass::MY_CONST;
            EOS,

            <<<EOS
            <?php
            return (new (\Asmblah\PhpCodeShift\Shifter\Hook\ClassHooks::getReplacement('My\\\Stuff\\\MyClass'))())->myInstanceMethod() .
                   ' and also ' .
                   (\Asmblah\PhpCodeShift\Shifter\Hook\ClassHooks::getReplacement('My\\\Stuff\\\MyClass'))::myStaticMethod() .
                   ' and then ' .
                   (\Asmblah\PhpCodeShift\Shifter\Hook\ClassHooks::getReplacement('My\\\Stuff\\\MyClass'))::MY_CONST;
            EOS,
        ];

        yield 'references split different lines have line numbers preserved' => [
            <<<EOS
            <?php
            return (new \My\Stuff\MyClass)->myInstanceMethod(
                   ) .
                   ' and also ' .
                   \My\Stuff\MyClass::
                   
                   
                       myStaticMethod() .
                   ' and then ' .
                   \My\Stuff\MyClass
                   
                   ::MY_CONST;
            EOS,

            // Note indentation and `::` position are _not_ preserved.
            <<<EOS
            <?php
            return (new (\Asmblah\PhpCodeShift\Shifter\Hook\ClassHooks::getReplacement('My\\\Stuff\\\MyClass'))())->myInstanceMethod(
                   ) .
                   ' and also ' .
                   (\Asmblah\PhpCodeShift\Shifter\Hook\ClassHooks::getReplacement('My\\\Stuff\\\MyClass'))::
            
            
            myStaticMethod() .
                   ' and then ' .
                   (\Asmblah\PhpCodeShift\Shifter\Hook\ClassHooks::getReplacement('My\\\Stuff\\\MyClass'))::
            
            MY_CONST;
            EOS,
        ];
    }
}
