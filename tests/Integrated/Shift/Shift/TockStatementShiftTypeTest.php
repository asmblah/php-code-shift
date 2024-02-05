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
use PhpParser\Node\Name\FullyQualified;
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
                            new FullyQualified('My\Stuff\MyTockHandler'),
                            'tock'
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
    \My\Stuff\MyTockHandler::tock();return $myNumber * 2;
}

return doubleIt(21);
EOS,
        ];

        yield 'empty global function with no whitespace between braces' => [
            <<<'EOS'
<?php
function doNothing(int $myNumber): void
{}

doNothing(21);
EOS,
            <<<'EOS'
<?php
function doNothing(int $myNumber): void
{\My\Stuff\MyTockHandler::tock();}

doNothing(21);
EOS,
        ];

        yield 'empty global function with whitespace between braces' => [
            <<<'EOS'
<?php
function doNothing(int $myNumber): void
{

}

doNothing(21);
EOS,
            <<<'EOS'
<?php
function doNothing(int $myNumber): void
{

\My\Stuff\MyTockHandler::tock();}

doNothing(21);
EOS,
        ];

        yield 'empty global function with comment containing brace between braces' => [
            <<<'EOS'
<?php
function doNothing(int $myNumber): void
{
/* This is my } comment */
}

doNothing(21);
EOS,
            <<<'EOS'
<?php
function doNothing(int $myNumber): void
{
/* This is my } comment */\My\Stuff\MyTockHandler::tock();
}

doNothing(21);
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
    \My\Stuff\MyTockHandler::tock();return $myNumber * 2;
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
        \My\Stuff\MyTockHandler::tock();return $myNumber * 2;
    }
}

return MyClass::doubleIt(21);
EOS,
        ];

        yield 'empty static method with no whitespace between braces' => [
            <<<'EOS'
<?php
class MyClass
{
    public static function doubleIt(int $myNumber): int
    {}
}

return MyClass::doubleIt(21);
EOS,
            <<<'EOS'
<?php
class MyClass
{
    public static function doubleIt(int $myNumber): int
    {\My\Stuff\MyTockHandler::tock();}
}

return MyClass::doubleIt(21);
EOS,
        ];

        yield 'empty static method with whitespace between braces' => [
            <<<'EOS'
<?php
class MyClass
{
    public static function doubleIt(int $myNumber): int
    {

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

    \My\Stuff\MyTockHandler::tock();}
}

return MyClass::doubleIt(21);
EOS,
        ];

        yield 'empty static method with comment containing brace between braces' => [
            <<<'EOS'
<?php
class MyClass
{
    public static function doubleIt(int $myNumber): int
    {
        /* This is my } comment */
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
        /* This is my } comment */\My\Stuff\MyTockHandler::tock();
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
        \My\Stuff\MyTockHandler::tock();return $myNumber * 2;
    }
}

return (new MyClass)->doubleIt(21);
EOS,
        ];

        yield 'static method of abstract class (shift still applies)' => [
            <<<'EOS'
<?php
abstract class MyClass
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
abstract class MyClass
{
    public static function doubleIt(int $myNumber): int
    {
        \My\Stuff\MyTockHandler::tock();return $myNumber * 2;
    }
}

return MyClass::doubleIt(21);
EOS,
        ];

        yield 'instance method of abstract class (shift still applies)' => [
            <<<'EOS'
<?php
abstract class MyClass
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
abstract class MyClass
{
    public function doubleIt(int $myNumber): int
    {
        \My\Stuff\MyTockHandler::tock();return $myNumber * 2;
    }
}

return (new MyClass)->doubleIt(21);
EOS,
        ];

        yield 'empty constructor method alone' => [
            <<<'EOS'
<?php
class MyClass
{
    public function __construct()
    {
    }
}

EOS,
            <<<'EOS'
<?php
class MyClass
{
    public function __construct()
    {
    \My\Stuff\MyTockHandler::tock();}
}

EOS,
        ];

        yield 'instance method followed by empty instance method' => [
            <<<'EOS'
<?php
class MyClass
{
    public function myFirstMethod(): int
    {
        return 21;
    }

    public function myEmptyMethod(int $myParam)
    {
    }
}

EOS,
            <<<'EOS'
<?php
class MyClass
{
    public function myFirstMethod(): int
    {
        \My\Stuff\MyTockHandler::tock();return 21;
    }

    public function myEmptyMethod(int $myParam)
    {
    \My\Stuff\MyTockHandler::tock();}
}

EOS,
        ];

        yield 'instance method followed by non-empty constructor' => [
            <<<'EOS'
<?php
class MyClass
{
    private $myProp;

    public function myMethod(): int
    {
        return 21;
    }

    public function __construct(int $myParam)
    {
        $this->myProp = $myParam;
    }
}

EOS,
            <<<'EOS'
<?php
class MyClass
{
    private $myProp;

    public function myMethod(): int
    {
        \My\Stuff\MyTockHandler::tock();return 21;
    }

    public function __construct(int $myParam)
    {
        \My\Stuff\MyTockHandler::tock();$this->myProp = $myParam;
    }
}

EOS,
        ];

        yield 'constructor followed by instance method' => [
            <<<'EOS'
<?php
class MyClass
{
    public function __construct(int $myParam)
    {
    }

    public function myMethod(): int
    {
        return 21;
    }
}

EOS,
            <<<'EOS'
<?php
class MyClass
{
    public function __construct(int $myParam)
    {
    \My\Stuff\MyTockHandler::tock();}

    public function myMethod(): int
    {
        \My\Stuff\MyTockHandler::tock();return 21;
    }
}

EOS,
        ];

        yield 'mix of static and instance methods' => [
            <<<'EOS'
<?php
class MyClass
{
    public static function doubleIt(int $myNumber): int
    {
        return $myNumber * 2;
    }

    public function tripleIt(int $myNumber): int
    {
        return $myNumber * 3;
    }

    public static function quadrupleIt(int $myNumber): int
    {
        return $myNumber * 4;
    }
}

MyClass::doubleIt(10);
MyClass::quadrupleIt(11);

return (new MyClass)->tripleIt(12);
EOS,
            <<<'EOS'
<?php
class MyClass
{
    public static function doubleIt(int $myNumber): int
    {
        \My\Stuff\MyTockHandler::tock();return $myNumber * 2;
    }

    public function tripleIt(int $myNumber): int
    {
        \My\Stuff\MyTockHandler::tock();return $myNumber * 3;
    }

    public static function quadrupleIt(int $myNumber): int
    {
        \My\Stuff\MyTockHandler::tock();return $myNumber * 4;
    }
}

MyClass::doubleIt(10);
MyClass::quadrupleIt(11);

return (new MyClass)->tripleIt(12);
EOS,
        ];





        yield 'abstract static method (no shift applies)' => [
            <<<'EOS'
<?php
class MyClass
{
    abstract public static function doubleIt(int $myNumber): int;
}
EOS,
            <<<'EOS'
<?php
class MyClass
{
    abstract public static function doubleIt(int $myNumber): int;
}
EOS,
        ];

        yield 'abstract instance method (no shift applies)' => [
            <<<'EOS'
<?php
class MyClass
{
    abstract public function doubleIt(int $myNumber): int;
}
EOS,
            <<<'EOS'
<?php
class MyClass
{
    abstract public function doubleIt(int $myNumber): int;
}
EOS,
        ];

        yield 'interface method (no shift applies)' => [
            <<<'EOS'
<?php
interface MyInterface
{
    public function doubleIt(int $myNumber): int;
}
EOS,
            <<<'EOS'
<?php
interface MyInterface
{
    public function doubleIt(int $myNumber): int;
}
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
    \My\Stuff\MyTockHandler::tock();doFirstThing(
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
    \My\Stuff\MyTockHandler::tock();doFirstThing(
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
    \My\Stuff\MyTockHandler::tock();doFirstThing(
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
    \My\Stuff\MyTockHandler::tock();doFirstThing(
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
    \My\Stuff\MyTockHandler::tock();print 'Before';

    while (    $myCondition    ) {
        \My\Stuff\MyTockHandler::tock();doFirstThing(
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
    \My\Stuff\MyTockHandler::tock();return $myNumber * 2;
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
