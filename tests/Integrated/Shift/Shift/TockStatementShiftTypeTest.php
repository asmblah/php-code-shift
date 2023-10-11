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
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\Tock\TockStatementShiftType;
use Asmblah\PhpCodeShift\Shifter\Shift\Shifter\ShiftSetShifterInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\ShiftSet;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Generator;
use Mockery\MockInterface;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;

/**
 * Class TockStatementShiftTypeTest.
 *
 * Tests the code transpilation logic of TockStatementShiftType.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class TockStatementShiftTypeTest extends AbstractTestCase
{
    private DelegatingShift $delegatingShift;
    private MockInterface&DenyListInterface $denyList;
    private MockInterface&FileFilterInterface $fileFilter;
    private ShiftSet $shiftSet;
    private ShiftSetShifterInterface $shiftSetShifter;

    public function setUp(): void
    {
        $this->delegatingShift = new DelegatingShift();
        $this->delegatingShift->registerShiftType(new TockStatementShiftType());
        $this->denyList = mock(DenyListInterface::class);
        $this->fileFilter = mock(FileFilterInterface::class);
        $this->shiftSetShifter = Shared::getStreamShifter()->getShiftSetShifter();

        $this->shiftSet = new ShiftSet('/my/path/to/my_module.php', [
            new Shift(
                $this->delegatingShift,
                new Shift\Tock\TockStatementShiftSpec(function () {
                    return new Expression(
                        new StaticCall(
                            new Name('My\Stuff\MyClass'),
                            'myMethod'
                        )
                    );
                }),
                $this->denyList,
                $this->fileFilter
            ),
        ]);
    }

    /**
     * @dataProvider dataProviderStringReplacement
     */
    public function testShiftGeneratesCorrectCode(
        string $input,
        string $expectedOutput
    ): void {
        static::assertSame($expectedOutput, $this->shiftSetShifter->shift($input, $this->shiftSet));
    }

    public static function dataProviderStringReplacement(): Generator
    {
        yield 'simple return in top-level scope (no shift applies)' => [
            '<?php return "this is my string";',
            '<?php return "this is my string";',
        ];

        yield 'global function definition and return statement' => [
            <<<'EOS'
<?php
function doubleIt(int $myNumber): int
{
    return $myNumber * 2;
}

return doubleIt(21);
EOS,
            <<<'EOS'
<?php
function doubleIt(int $myNumber): int
{
    My\Stuff\MyClass::myMethod();return $myNumber * 2;
}

return doubleIt(21);
EOS,
        ];

        yield 'closure' => [
            <<<'EOS'
<?php
$doubleIt = function (int $myNumber): int {
    return $myNumber * 2;
};

return $doubleIt(21);
EOS,
            <<<'EOS'
<?php
$doubleIt = function (int $myNumber): int {
    My\Stuff\MyClass::myMethod();return $myNumber * 2;
};

return $doubleIt(21);
EOS,
        ];

        yield 'static method' => [
            <<<'EOS'
<?php
class MyClass
{
    public static function doubleIt(int $myNumber): int
    {
        return $myNumber * 2;
    }
}

return MyClass::doubleIt(21);
EOS,
            <<<'EOS'
<?php
class MyClass
{
    public static function doubleIt(int $myNumber): int
    {
        My\Stuff\MyClass::myMethod();return $myNumber * 2;
    }
}

return MyClass::doubleIt(21);
EOS,
        ];

        yield 'instance method' => [
            <<<'EOS'
<?php
class MyClass
{
    public function doubleIt(int $myNumber): int
    {
        return $myNumber * 2;
    }
}

return (new MyClass)->doubleIt(21);
EOS,
            <<<'EOS'
<?php
class MyClass
{
    public function doubleIt(int $myNumber): int
    {
        My\Stuff\MyClass::myMethod();return $myNumber * 2;
    }
}

return (new MyClass)->doubleIt(21);
EOS,
        ];

        // Note use of extraneous whitespace to check handling.
        yield 'do...while loop in top-level scope' => [
            <<<'EOS'
<?php
do {
    doFirstThing(
        $arg1,
        $arg2
    );
    doSecondThing(  $onlyArg  );
} while (    $myCondition    );
EOS,
            <<<'EOS'
<?php
do {
    My\Stuff\MyClass::myMethod();doFirstThing(
        $arg1,
        $arg2
    );
    doSecondThing(  $onlyArg  );
} while (    $myCondition    );
EOS,
        ];

        yield 'for loop in top-level scope' => [
            <<<'EOS'
<?php
for ($i = 0; $i < 10; $i++) {
    doFirstThing(
        $arg1,
        $arg2
    );
    doSecondThing(  $onlyArg  );
}
EOS,
            <<<'EOS'
<?php
for ($i = 0; $i < 10; $i++) {
    My\Stuff\MyClass::myMethod();doFirstThing(
        $arg1,
        $arg2
    );
    doSecondThing(  $onlyArg  );
}
EOS,
        ];

        yield 'foreach loop in top-level scope' => [
            <<<'EOS'
<?php
foreach ($myList as $myKey => $myValue) {
    doFirstThing(
        $arg1,
        $arg2
    );
    doSecondThing(  $onlyArg  );
}
EOS,
            <<<'EOS'
<?php
foreach ($myList as $myKey => $myValue) {
    My\Stuff\MyClass::myMethod();doFirstThing(
        $arg1,
        $arg2
    );
    doSecondThing(  $onlyArg  );
}
EOS,
        ];

        yield 'while loop in top-level scope' => [
            <<<'EOS'
<?php
while (    $myCondition    ) {
    doFirstThing(
        $arg1,
        $arg2
    );
    doSecondThing(  $onlyArg  );
}
EOS,
            <<<'EOS'
<?php
while (    $myCondition    ) {
    My\Stuff\MyClass::myMethod();doFirstThing(
        $arg1,
        $arg2
    );
    doSecondThing(  $onlyArg  );
}
EOS,
        ];

        // Test the combination of a function containing a loop structure, where both statements
        // should have the tock statement inserted at the beginning.
        yield 'while loop inside global function' => [
            <<<'EOS'
<?php

function myFunction(): int
{
    print 'Before';

    while (    $myCondition    ) {
        doFirstThing(
            $arg1,
            $arg2
        );
        doSecondThing(  $onlyArg  );
    }

    print 'After';
}

return myFunction();
EOS,
            <<<'EOS'
<?php

function myFunction(): int
{
    My\Stuff\MyClass::myMethod();print 'Before';

    while (    $myCondition    ) {
        My\Stuff\MyClass::myMethod();doFirstThing(
            $arg1,
            $arg2
        );
        doSecondThing(  $onlyArg  );
    }

    print 'After';
}

return myFunction();
EOS,
        ];

        yield 'Tockless attribute prevents tocks being applied' => [
            <<<'EOS'
<?php

use Asmblah\PhpCodeShift\Attribute\Tockless;

function myTockedFunction(int $myNumber): int
{
    return $myNumber * 2;
}

#[Tockless]
function myUntockedFunction(int $myNumber): int
{
    return $myNumber * 3;
}

return doubleIt(21);
EOS,
            <<<'EOS'
<?php

use Asmblah\PhpCodeShift\Attribute\Tockless;

function myTockedFunction(int $myNumber): int
{
    My\Stuff\MyClass::myMethod();return $myNumber * 2;
}

#[Tockless]
function myUntockedFunction(int $myNumber): int
{
    return $myNumber * 3;
}

return doubleIt(21);
EOS,
        ];
    }
}
