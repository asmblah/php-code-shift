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
use Asmblah\PhpCodeShift\Shifter\Printer\NodeType\StaticCallNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\PrintedNodeCollectionInterface;
use Asmblah\PhpCodeShift\Shifter\Printer\PrintedNodeInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Context\ModificationContextInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery\MockInterface;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;

/**
 * Class StaticCallNodePrinterTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StaticCallNodePrinterTest extends AbstractTestCase
{
    private MockInterface&Arg $argument1Node;
    private MockInterface&Arg $argument2Node;
    private MockInterface&Name $classNameNode;
    private MockInterface&Identifier $methodNameNode;
    private MockInterface&ModificationContextInterface $modificationContext;
    private StaticCall $node;
    private MockInterface&NodePrinterInterface $nodePrinter;
    private MockInterface&PrintedNodeCollectionInterface $printedArgsNodeCollection;
    private MockInterface&PrintedNodeInterface $printedClassNameNode;
    private MockInterface&PrintedNodeInterface $printedMethodNameNode;
    private StaticCallNodePrinter $printer;

    public function setUp(): void
    {
        $this->argument1Node = mock(Arg::class);
        $this->argument2Node = mock(Arg::class);
        $this->classNameNode = mock(Name::class);
        $this->methodNameNode = mock(Identifier::class);
        $this->modificationContext = mock(ModificationContextInterface::class);
        $this->node = new StaticCall(
            $this->classNameNode,
            $this->methodNameNode,
            [$this->argument1Node, $this->argument2Node]
        );
        $this->printedClassNameNode = mock(PrintedNodeInterface::class, [
            'getCode' => '\My\Namespace\MyPrintedClass',
            'getEndLine' => 25,
        ]);
        $this->printedMethodNameNode = mock(PrintedNodeInterface::class, [
            'getCode' => 'myPrintedMethod',
            'getEndLine' => 30,
        ]);
        $this->printedArgsNodeCollection = mock(PrintedNodeCollectionInterface::class, [
            'getEndLine' => 35,
        ]);
        $this->nodePrinter = mock(NodePrinterInterface::class);

        $this->nodePrinter->allows()
            ->printNode($this->classNameNode, 21, $this->modificationContext)
            ->andReturn($this->printedClassNameNode);
        $this->nodePrinter->allows()
            ->printNode($this->methodNameNode, 25, $this->modificationContext)
            ->andReturn($this->printedMethodNameNode);
        $this->nodePrinter->allows()
            ->printNodeCollection(
                [$this->argument1Node, $this->argument2Node],
                30,
                $this->modificationContext
            )
            ->andReturn($this->printedArgsNodeCollection);

        $this->printedArgsNodeCollection->allows()
            ->join(', ')
            ->andReturn('"first arg", "second arg"');

        $this->printer = new StaticCallNodePrinter($this->nodePrinter);
    }

    public function testGetNodeClassNameReturnsCorrectClass(): void
    {
        static::assertSame(StaticCall::class, $this->printer->getNodeClassName());
    }

    public function testPrintNodeBuildsACorrectPrintedNode(): void
    {
        $printedNode = $this->printer->printNode($this->node, 21, $this->modificationContext);

        static::assertSame(
            '\My\Namespace\MyPrintedClass::myPrintedMethod("first arg", "second arg")',
            $printedNode->getCode()
        );
        static::assertSame(21, $printedNode->getStartLine());
        static::assertSame(35, $printedNode->getEndLine());
    }
}
