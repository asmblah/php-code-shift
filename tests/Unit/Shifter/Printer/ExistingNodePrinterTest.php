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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter\Printer;

use Asmblah\PhpCodeShift\Shifter\Printer\ExistingNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Code\Context\ModificationContextInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Generator;
use LogicException;
use Mockery\MockInterface;
use PhpParser\Node;

/**
 * Class ExistingNodePrinterTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ExistingNodePrinterTest extends AbstractTestCase
{
    private MockInterface&ModificationContextInterface $modificationContext;
    private MockInterface&Node $node;
    private ExistingNodePrinter $printer;

    public function setUp(): void
    {
        $this->modificationContext = mock(ModificationContextInterface::class);
        $this->node = mock(Node::class);

        $this->printer = new ExistingNodePrinter();
    }

    /**
     * @dataProvider printNodeProvider
     */
    public function testPrintNodePrintsCorrectly(
        string $contents,
        int $line,
        int $delta,
        int $startFilePos,
        int $endFilePos,
        int $startLine,
        int $endLine,
        string $expectedReplacementCode,
        int $expectedStartLine,
        int $expectedEndLine
    ): void {
        $this->node->allows()
            ->getStartFilePos()
            ->andReturn($startFilePos);
        $this->node->allows()
            ->getEndFilePos()
            ->andReturn($endFilePos);
        $this->node->allows()
            ->getStartLine()
            ->andReturn($startLine);
        $this->node->allows()
            ->getEndLine()
            ->andReturn($endLine);
        $this->modificationContext->allows()
            ->getDelta()
            ->andReturn($delta);
        $this->modificationContext->allows()
            ->getContents()
            ->andReturn($contents);

        $printedNode = $this->printer->printNode($this->node, $line, $this->modificationContext);

        static::assertSame($expectedReplacementCode, $printedNode->getCode());
        static::assertSame($expectedStartLine, $printedNode->getStartLine());
        static::assertSame($expectedEndLine, $printedNode->getEndLine());
    }

    public static function printNodeProvider(): Generator
    {
        yield 'simple single line, delta=0, same line is current' => [
            '<?php myFunc();',
            1,
            0,
            6,
            11,
            1,
            1,
            'myFunc',
            1,
            1
        ];

        yield 'simple single line, delta=2, same line is current' => [
            '<?php myFunc();',
            1,
            2,
            6 - 2,
            11 - 2,
            1,
            1,
            'myFunc',
            1,
            1
        ];

        yield 'simple single line, delta=1, earlier line is current' => [
            '<?php myFunc();',
            2,
            1,
            6 - 1,
            11 - 1,
            5,
            8,
            "\n\n\nmyFunc",
            5,
            8
        ];
    }

    public function testPrintNodeRaisesLogicExceptionWhenImpossibleLineIsCurrent(): void
    {
        $this->node->allows()
            ->getStartFilePos()
            ->andReturn(10);
        $this->node->allows()
            ->getEndFilePos()
            ->andReturn(15);
        $this->node->allows()
            ->getStartLine()
            ->andReturn(3);
        $this->node->allows()
            ->getEndLine()
            ->andReturn(5);
        $this->modificationContext->allows()
            ->getDelta()
            ->andReturn(0);
        $this->modificationContext->allows()
            ->getContents()
            ->andReturn('<?php print "Hello world, this is my program.";');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Impossible to match original line of 3 when current line of 4 is beyond it'
        );

        $this->printer->printNode($this->node, 4, $this->modificationContext);
    }
}
