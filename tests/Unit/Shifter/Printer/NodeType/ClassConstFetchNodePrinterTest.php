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
use Asmblah\PhpCodeShift\Shifter\Printer\NodeType\ClassConstFetchNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\PrintedNodeInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Code\Context\ModificationContextInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery\MockInterface;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;

/**
 * Class ClassConstFetchNodePrinterTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ClassConstFetchNodePrinterTest extends AbstractTestCase
{
    private MockInterface&Name $classNameNode;
    private MockInterface&Identifier $constantNameNode;
    private MockInterface&ModificationContextInterface $modificationContext;
    private ClassConstFetch $node;
    private MockInterface&NodePrinterInterface $nodePrinter;
    private MockInterface&PrintedNodeInterface $printedClassNameNode;
    private MockInterface&PrintedNodeInterface $printedConstantNameNode;
    private ClassConstFetchNodePrinter $printer;

    public function setUp(): void
    {
        $this->classNameNode = mock(Name::class);
        $this->constantNameNode = mock(Identifier::class);
        $this->modificationContext = mock(ModificationContextInterface::class);
        $this->node = new ClassConstFetch(
            $this->classNameNode,
            $this->constantNameNode
        );
        $this->printedClassNameNode = mock(PrintedNodeInterface::class, [
            'getCode' => '\My\Namespace\MyPrintedClass',
            'getEndLine' => 25,
        ]);
        $this->printedConstantNameNode = mock(PrintedNodeInterface::class, [
            'getCode' => 'MY_PRINTED_CONST',
            'getEndLine' => 30,
        ]);
        $this->nodePrinter = mock(NodePrinterInterface::class);

        $this->nodePrinter->allows()
            ->printNode($this->classNameNode, 21, $this->modificationContext)
            ->andReturn($this->printedClassNameNode)
            ->byDefault();
        $this->nodePrinter->allows()
            ->printNode($this->constantNameNode, 25, $this->modificationContext)
            ->andReturn($this->printedConstantNameNode)
            ->byDefault();

        $this->printer = new ClassConstFetchNodePrinter($this->nodePrinter);
    }

    public function testGetNodeClassNameReturnsCorrectClass(): void
    {
        static::assertSame(ClassConstFetch::class, $this->printer->getNodeClassName());
    }

    public function testPrintNodeBuildsACorrectPrintedNodeWhenClassIsJustABarewordName(): void
    {
        $printedNode = $this->printer->printNode($this->node, 21, $this->modificationContext);

        static::assertSame(
            '\My\Namespace\MyPrintedClass::MY_PRINTED_CONST',
            $printedNode->getCode()
        );
        static::assertSame(21, $printedNode->getStartLine());
        static::assertSame(30, $printedNode->getEndLine());
    }

    public function testPrintNodeSurroundsWithParenthesesWhenClassIsAnExpression(): void
    {
        $classNameNode = mock(Expr::class);
        $this->nodePrinter->allows()
            ->printNode($classNameNode, 21, $this->modificationContext)
            ->andReturn($this->printedClassNameNode);
        $this->node->class = $classNameNode;

        $printedNode = $this->printer->printNode($this->node, 21, $this->modificationContext);

        static::assertSame(
            '(\My\Namespace\MyPrintedClass)::MY_PRINTED_CONST',
            $printedNode->getCode()
        );
        static::assertSame(21, $printedNode->getStartLine());
        static::assertSame(30, $printedNode->getEndLine());
    }

    public function testPrintNodeBuildsACorrectPrintedNodeWhenClassAndConstantAreExpressions(): void
    {
        $classNameNode = mock(Expr::class);
        $this->nodePrinter->allows()
            ->printNode($classNameNode, 21, $this->modificationContext)
            ->andReturn($this->printedClassNameNode);
        $this->node->class = $classNameNode;
        $constantNameNode = mock(Expr::class);
        $this->nodePrinter->allows()
            ->printNode($constantNameNode, 25, $this->modificationContext)
            ->andReturn($this->printedConstantNameNode);
        $this->printedConstantNameNode->allows()
            ->getCode()
            ->andReturn('$myPrefix . $mySuffix');
        $this->node->name = $constantNameNode;

        $printedNode = $this->printer->printNode($this->node, 21, $this->modificationContext);

        static::assertSame(
            '(\My\Namespace\MyPrintedClass)::{$myPrefix . $mySuffix}',
            $printedNode->getCode()
        );
        static::assertSame(21, $printedNode->getStartLine());
        static::assertSame(30, $printedNode->getEndLine());
    }
}
