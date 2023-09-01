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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter\Shift\Shift\FunctionHook;

use Asmblah\PhpCodeShift\Shifter\Parser\ParserFactory;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\FunctionHook\FunctionHookShiftSpec;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\FunctionHook\FunctionHookShiftType;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery\MockInterface;
use PhpParser\Parser;
use PhpParser\ParserFactory as LibraryParserFactory;

/**
 * Class FunctionHookShiftTypeTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class FunctionHookShiftTypeTest extends AbstractTestCase
{
    private ?Parser $parser;
    /**
     * @var (MockInterface&FunctionHookShiftSpec)|null
     */
    private $shiftSpec;
    private ?FunctionHookShiftType $shiftType;

    public function setUp(): void
    {
        $this->parser = (new ParserFactory(new LibraryParserFactory()))->createParser();
        $this->shiftSpec = mock(FunctionHookShiftSpec::class, [
            'getFunctionName' => 'myFunc',
        ]);

        $this->shiftType = new FunctionHookShiftType($this->parser);
    }

    public function testShiftHooksTheSpecifiedFunctionWhenCalledOnceWithNoArguments(): void
    {
        $result = $this->shiftType->shift($this->shiftSpec, '<?php print myFunc();');

        static::assertSame('<?php print \Asmblah\PhpCodeShift\Shifter\Hook\Invoker::myFunc();', $result);
    }

    public function testShiftHooksTheSpecifiedFunctionWhenCalledOnceWithTwoArguments(): void
    {
        $result = $this->shiftType->shift($this->shiftSpec, '<?php print myFunc(21, "hello");');

        static::assertSame('<?php print \Asmblah\PhpCodeShift\Shifter\Hook\Invoker::myFunc(21, "hello");', $result);
    }

    public function testShiftHooksTheSpecifiedFunctionWhenCalledTwiceSeparately(): void
    {
        $result = $this->shiftType->shift($this->shiftSpec, '<?php print myFunc(); echo myFunc();');

        static::assertSame(
            '<?php print \Asmblah\PhpCodeShift\Shifter\Hook\Invoker::myFunc(); ' .
            'echo \Asmblah\PhpCodeShift\Shifter\Hook\Invoker::myFunc();',
            $result
        );
    }

    public function testShiftHooksTheSpecifiedFunctionWhenCalledTwiceNested(): void
    {
        $result = $this->shiftType->shift($this->shiftSpec, '<?php print myFunc(myFunc());');

        static::assertSame(
            '<?php print \Asmblah\PhpCodeShift\Shifter\Hook\Invoker::myFunc(\Asmblah\PhpCodeShift\Shifter\Hook\Invoker::myFunc());',
            $result
        );
    }

    public function testShiftReturnsStringUnchangedWhenNoFunctionIsCalled(): void
    {
        $result = $this->shiftType->shift($this->shiftSpec, '<?php print 21;');

        static::assertSame('<?php print 21;', $result);
    }

    public function testShiftReturnsStringUnchangedWhenADifferentFunctionIsCalled(): void
    {
        $result = $this->shiftType->shift($this->shiftSpec, '<?php print yourFunc();');

        static::assertSame('<?php print yourFunc();', $result);
    }
}
