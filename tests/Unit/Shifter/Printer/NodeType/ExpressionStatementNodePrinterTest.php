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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter\Printer\NodeType;

use Asmblah\PhpCodeShift\Shifter\Printer\NodePrinterInterface;
use Asmblah\PhpCodeShift\Shifter\Printer\NodeType\ExpressionStatementNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\PrintedNodeInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Code\Context\ModificationContextInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery\MockInterface;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Expression;

/**
 * Class ExpressionStatementNodePrinterTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ExpressionStatementNodePrinterTest extends AbstractTestCase
{
    private MockInterface&Expr $expressionNode;
    private MockInterface&ModificationContextInterface $modificationContext;
    private MockInterface&NodePrinterInterface $nodePrinter;
    private MockInterface&PrintedNodeInterface $printedExpressionNode;
    private ExpressionStatementNodePrinter $printer;
    private Expression $statementNode;

    public function setUp(): void
    {
        $this->expressionNode = mock(Expr::class);
        $this->modificationContext = mock(ModificationContextInterface::class);
        $this->nodePrinter = mock(NodePrinterInterface::class);
        $this->printedExpressionNode = mock(PrintedNodeInterface::class, [
            'getCode' => '$my($printedExpression)',
            'getStartLine' => 10,
            'getEndLine' => 12,
        ]);
        $this->statementNode = new Expression($this->expressionNode);

        $this->nodePrinter->allows()
            ->printNode($this->expressionNode, 21, $this->modificationContext)
            ->andReturn($this->printedExpressionNode)
            ->byDefault();

        $this->printer = new ExpressionStatementNodePrinter($this->nodePrinter);
    }

    public function testGetNodeClassNameReturnsCorrectClass(): void
    {
        static::assertSame(Expression::class, $this->printer->getNodeClassName());
    }

    public function testPrintNodeReturnsACorrectPrintedNode(): void
    {
        $printedNode = $this->printer->printNode($this->statementNode, 21, $this->modificationContext);

        static::assertSame('$my($printedExpression);', $printedNode->getCode(), 'Semicolon should be appended');
        static::assertSame(10, $printedNode->getStartLine());
        static::assertSame(12, $printedNode->getEndLine());
    }
}
