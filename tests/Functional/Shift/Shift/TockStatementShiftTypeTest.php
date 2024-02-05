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
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Expression;

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
                        new FullyQualified(TockHandler::class),
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
        include __DIR__ . '/../../Fixtures/tock/tock_global_functions.php';

        static::assertEquals(
            [
                'tock() :: Asmblah\PhpCodeShift\Tests\Functional\Fixtures\myFirstFunction @ line 11',
                'log() :: Hello from myFirstFunction',
                'tock() :: Asmblah\PhpCodeShift\Tests\Functional\Fixtures\mySecondFunction @ line 18',
                'log() :: Hello from mySecondFunction',
            ],
            TockHandler::getLogs()
        );
    }

    public function testCanHookClosures(): void
    {
        include __DIR__ . '/../../Fixtures/tock/tock_closures.php';

        static::assertEquals(
            [
                'tock() :: Asmblah\PhpCodeShift\Tests\Functional\Fixtures\{closure} @ line 16',
                'log() :: Hello from myFirstClosure',
                'tock() :: Asmblah\PhpCodeShift\Tests\Functional\Fixtures\{closure} @ line 10',
                'log() :: Hello from mySecondClosure',
            ],
            TockHandler::getLogs()
        );
    }

    public function testCanHookDoWhileLoops(): void
    {
        include __DIR__ . '/../../Fixtures/tock/tock_do_while_loop.php';

        static::assertEquals(
            [
                'tock() :: include @ line 8',
                'log() :: Iteration $i=0',
                'tock() :: include @ line 8',
                'log() :: Iteration $i=1',
                'tock() :: include @ line 8',
                'log() :: Iteration $i=2',
                'tock() :: include @ line 8',
                'log() :: Iteration $i=3',
                'tock() :: include @ line 8',
                'log() :: Iteration $i=4',
            ],
            TockHandler::getLogs()
        );
    }

    public function testCanHookForLoops(): void
    {
        include __DIR__ . '/../../Fixtures/tock/tock_for_loop.php';

        static::assertEquals(
            [
                'tock() :: include @ line 6',
                'log() :: Iteration #0',
                'tock() :: include @ line 6',
                'log() :: Iteration #1',
                'tock() :: include @ line 6',
                'log() :: Iteration #2',
                'tock() :: include @ line 6',
                'log() :: Iteration #3',
            ],
            TockHandler::getLogs()
        );
    }

    public function testCanHookForeachLoops(): void
    {
        include __DIR__ . '/../../Fixtures/tock/tock_foreach_loop.php';

        static::assertEquals(
            [
                'tock() :: include @ line 8',
                'log() :: Iteration $myKey=first, $myValue=one',
                'tock() :: include @ line 8',
                'log() :: Iteration $myKey=second, $myValue=two',
                'tock() :: include @ line 8',
                'log() :: Iteration $myKey=third, $myValue=three',
            ],
            TockHandler::getLogs()
        );
    }

    public function testCanHookWhileLoops(): void
    {
        include __DIR__ . '/../../Fixtures/tock/tock_while_loop.php';

        static::assertEquals(
            [
                'tock() :: include @ line 8',
                'log() :: Iteration $i=1',
                'tock() :: include @ line 8',
                'log() :: Iteration $i=2',
                'tock() :: include @ line 8',
                'log() :: Iteration $i=3',
                'tock() :: include @ line 8',
                'log() :: Iteration $i=4',
            ],
            TockHandler::getLogs()
        );
    }
}
