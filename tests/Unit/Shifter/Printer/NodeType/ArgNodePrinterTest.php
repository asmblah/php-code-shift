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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter\Printer\NodeType;

use Asmblah\PhpCodeShift\Shifter\Printer\NodePrinterInterface;
use Asmblah\PhpCodeShift\Shifter\Printer\NodeType\ArgNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\PrintedNodeInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Code\Context\ModificationContextInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery\MockInterface;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;

/**
 * Class ArgNodePrinterTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ArgNodePrinterTest extends AbstractTestCase
{
    private MockInterface&ModificationContextInterface $modificationContext;
    private Arg $node;
    private MockInterface&NodePrinterInterface $nodePrinter;
    private MockInterface&PrintedNodeInterface $printedValueNode;
    private ArgNodePrinter $printer;
    private MockInterface&Expr $valueNode;

    public function setUp(): void
    {
        $this->valueNode = mock(Expr::class);
        $this->modificationContext = mock(ModificationContextInterface::class);
        $this->node = new Arg($this->valueNode);
        $this->printedValueNode = mock(PrintedNodeInterface::class, [
            'getCode' => 'myCall()',
            'getEndLine' => 25,
        ]);
        $this->nodePrinter = mock(NodePrinterInterface::class);

        $this->nodePrinter->allows()
            ->printNode($this->valueNode, 21, $this->modificationContext)
            ->andReturn($this->printedValueNode)
            ->byDefault();

        $this->printer = new ArgNodePrinter($this->nodePrinter);
    }

    public function testGetNodeClassNameReturnsCorrectClass(): void
    {
        static::assertSame(Arg::class, $this->printer->getNodeClassName());
    }

    public function testPrintNodeBuildsACorrectlyPrintedNodeWhenValueOnly(): void
    {
        $printedNode = $this->printer->printNode($this->node, 21, $this->modificationContext);

        static::assertSame(
            'myCall()',
            $printedNode->getCode()
        );
        static::assertSame(21, $printedNode->getStartLine());
        static::assertSame(25, $printedNode->getEndLine());
    }

    public function testPrintNodeBuildsACorrectlyPrintedNodeWhenNamed(): void
    {
        $nameNode = mock(Identifier::class);
        $printedNameNode = mock(PrintedNodeInterface::class, [
            'getCode' => 'myParam',
            'getEndLine' => 23,
        ]);
        $this->node->name = $nameNode;
        $this->nodePrinter->allows()
            ->printNode($nameNode, 21, $this->modificationContext)
            ->andReturn($printedNameNode);
        $this->nodePrinter->allows()
            ->printNode($this->valueNode, 23, $this->modificationContext)
            ->andReturn($this->printedValueNode);

        $printedNode = $this->printer->printNode($this->node, 21, $this->modificationContext);

        static::assertSame(
            'myParam: myCall()',
            $printedNode->getCode()
        );
        static::assertSame(21, $printedNode->getStartLine());
        static::assertSame(25, $printedNode->getEndLine());
    }
}
