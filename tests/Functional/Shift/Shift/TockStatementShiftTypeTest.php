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
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\Tock\TockStatementShiftSpec;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Asmblah\PhpCodeShift\Tests\Functional\Harness\Shift\Tock\TockHandler;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use function Asmblah\PhpCodeShift\Tests\Functional\Fixtures\myFirstFunction;

/**
 * Class TockStatementShiftTypeTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class TockStatementShiftTypeTest extends AbstractTestCase
{
    private CodeShift $codeShift;

    public function setUp(): void
    {
        $this->codeShift = new CodeShift();

        $this->codeShift->shift(
            new TockStatementShiftSpec(
                fn () => new Expression(
                    new StaticCall(
                        new Name('\\' . TockHandler::class),
                        new Identifier('tock')
                    )
                )
            ),
            new FileFilter(dirname(__DIR__, 2) . '/Fixtures/**')
        );
    }

    public function tearDown(): void
    {
        TockHandler::reset();

        $this->codeShift->uninstall();
    }

    public function testCanHookGlobalFunctions(): void
    {
        include __DIR__ . '/../../Fixtures/tock_global_functions.php';

        myFirstFunction(21);

        static::assertEquals(
            [
                'Asmblah\PhpCodeShift\Tests\Functional\Fixtures\myFirstFunction',
                'Asmblah\PhpCodeShift\Tests\Functional\Fixtures\mySecondFunction',
            ],
            TockHandler::getCalls()
        );
    }
}
