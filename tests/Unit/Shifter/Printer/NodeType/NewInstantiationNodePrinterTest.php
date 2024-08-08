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
use Asmblah\PhpCodeShift\Shifter\Printer\NodeType\NewInstantiationNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\PrintedNodeCollectionInterface;
use Asmblah\PhpCodeShift\Shifter\Printer\PrintedNodeInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Code\Context\ModificationContextInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery\MockInterface;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;

/**
 * Class NewInstantiationNodePrinterTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class NewInstantiationNodePrinterTest extends AbstractTestCase
{
    private MockInterface&Arg $argument1Node;
    private MockInterface&Arg $argument2Node;
    private MockInterface&Name $classNameNode;
    private MockInterface&ModificationContextInterface $modificationContext;
    private New_ $node;
    private MockInterface&NodePrinterInterface $nodePrinter;
    private MockInterface&PrintedNodeCollectionInterface $printedArgsNodeCollection;
    private MockInterface&PrintedNodeInterface $printedClassNameNode;
    private NewInstantiationNodePrinter $printer;

    public function setUp(): void
    {
        $this->argument1Node = mock(Arg::class);
        $this->argument2Node = mock(Arg::class);
        $this->classNameNode = mock(Name::class);
        $this->modificationContext = mock(ModificationContextInterface::class);
        $this->node = new New_(
            $this->classNameNode,
            [$this->argument1Node, $this->argument2Node]
        );
        $this->printedClassNameNode = mock(PrintedNodeInterface::class, [
            'getCode' => '\My\Namespace\MyPrintedClass',
            'getEndLine' => 25,
        ]);
        $this->printedArgsNodeCollection = mock(PrintedNodeCollectionInterface::class, [
            'getEndLine' => 35,
        ]);
        $this->nodePrinter = mock(NodePrinterInterface::class);

        $this->nodePrinter->allows()
            ->printNode($this->classNameNode, 21, $this->modificationContext)
            ->andReturn($this->printedClassNameNode);
        $this->nodePrinter->allows()
            ->printNodeCollection(
                [$this->argument1Node, $this->argument2Node],
                25,
                $this->modificationContext
            )
            ->andReturn($this->printedArgsNodeCollection);

        $this->printedArgsNodeCollection->allows()
            ->join(', ')
            ->andReturn('"first arg", "second arg"');

        $this->printer = new NewInstantiationNodePrinter($this->nodePrinter);
    }

    public function testGetNodeClassNameReturnsCorrectClass(): void
    {
        static::assertSame(New_::class, $this->printer->getNodeClassName());
    }

    public function testPrintNodeBuildsACorrectPrintedNodeWhenClassIsJustABarewordName(): void
    {
        $printedNode = $this->printer->printNode($this->node, 21, $this->modificationContext);

        static::assertSame(
            'new \My\Namespace\MyPrintedClass("first arg", "second arg")',
            $printedNode->getCode()
        );
        static::assertSame(21, $printedNode->getStartLine());
        static::assertSame(35, $printedNode->getEndLine());
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
            'new (\My\Namespace\MyPrintedClass)("first arg", "second arg")',
            $printedNode->getCode()
        );
        static::assertSame(21, $printedNode->getStartLine());
        static::assertSame(35, $printedNode->getEndLine());
    }
}
