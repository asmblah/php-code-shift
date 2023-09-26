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

namespace Asmblah\PhpCodeShift\Tests\Functional\Shift\Shift;

use Asmblah\PhpCodeShift\CodeShift;
use Asmblah\PhpCodeShift\Shifter\Filter\FileFilter;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\FunctionHook\FunctionHookShiftSpec;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;

/**
 * Class FunctionHookShiftTypeTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class FunctionHookShiftTypeTest extends AbstractTestCase
{
    private CodeShift $codeShift;

    public function setUp(): void
    {
        $this->codeShift = new CodeShift();

        $this->codeShift->shift(
            new FunctionHookShiftSpec(
                'substr',
                function (callable $originalSubstr) {
                    return function (string $string, int $offset, ?int $length = null) use ($originalSubstr) {
                        $originalResult = $originalSubstr($string, $offset, $length);

                        if ($string === 'my string' || $string === 'your string' || $string === 'hello') {
                            return '[substr<' . $originalResult . '>]';
                        }

                        return $originalResult;
                    };
                }
            ),
            new FileFilter(dirname(__DIR__, 2) . '/Fixtures/**')
        );
    }

    public function tearDown(): void
    {
        $this->codeShift->uninstall();
    }

    public function testCanHookBuiltinFunctionInModuleRootWhenInGlobalNamespace(): void
    {
        $result = include __DIR__ . '/../../Fixtures/substr_module_root_global_namespace_test.php';

        static::assertSame('[substr<y st>] and [substr<ou>]', $result);
    }

    public function testCanHookBuiltinFunctionSplitAcrossMultipleLinesInModuleRootWhenInGlobalNamespace(): void
    {
        $result = include __DIR__ . '/../../Fixtures/substr_multi_line_test.php';

        static::assertSame('[substr<y st>] and [substr<ou>]', $result);
    }

    public function testCanHookBuiltinFunctionInModuleRootWhenInsideNamespace(): void
    {
        $result = include __DIR__ . '/../../Fixtures/substr_module_root_namespace_test.php';

        static::assertSame('[substr<y st>] and [substr<ou>]', $result);
    }

    public function testCanHookBuiltinFunctionInPhar(): void
    {
        ob_start();
        include __DIR__ . '/../../Fixtures/phar/substr_in_phar.phar';
        $result = ob_get_clean();

        static::assertSame('[before] [substr<ll>] [after]', $result);
    }
}
