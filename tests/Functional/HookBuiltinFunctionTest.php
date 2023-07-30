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
use Asmblah\PhpCodeShift\Shifter\Shift\Spec\FunctionHookShiftSpec;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;

/**
 * Class HookBuiltinFunctionTest.
 *
 * Base class for all test cases.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class HookBuiltinFunctionTest extends AbstractTestCase
{
    private ?CodeShift $codeShift;

    public function setUp(): void
    {
        $this->codeShift = new CodeShift();

        $this->codeShift->shift(
            new FunctionHookShiftSpec(
                'substr',
                function (callable $originalSubstr) {
                    return function (string $string, int $offset, ?int $length = null) use ($originalSubstr) {
                        return '[substr<' . $originalSubstr($string, $offset, $length) . '>]';
                    };
                }
            ),
            new FileFilter(__DIR__ . '/Fixtures/**')
        );
    }

    public function tearDown(): void
    {
        $this->codeShift->uninstall();
    }

    public function testCanHookBuiltinFunctionInModuleRoot(): void
    {
        $result = include __DIR__ . '/Fixtures/substr_module_root_test.php';

        static::assertSame('[substr<y st>] and [substr<ou>]', $result);
    }
}
