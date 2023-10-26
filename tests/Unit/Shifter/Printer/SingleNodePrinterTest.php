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

use Asmblah\PhpCodeShift\Shifter\Printer\ExistingNodePrinterInterface;
use Asmblah\PhpCodeShift\Shifter\Printer\NewNodePrinterInterface;
use Asmblah\PhpCodeShift\Shifter\Printer\PrintedNodeInterface;
use Asmblah\PhpCodeShift\Shifter\Printer\SingleNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Code\Context\ModificationContextInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery\MockInterface;
use PhpParser\Node;

/**
 * Class SingleNodePrinterTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class SingleNodePrinterTest extends AbstractTestCase
{
    private MockInterface&ExistingNodePrinterInterface $existingNodePrinter;
    private MockInterface&ModificationContextInterface $modificationContext;
    private MockInterface&NewNodePrinterInterface $newNodePrinter;
    private MockInterface&Node $node;
    private MockInterface&PrintedNodeInterface $printedNode;
    private SingleNodePrinter $printer;

    public function setUp(): void
    {
        $this->existingNodePrinter = mock(ExistingNodePrinterInterface::class);
        $this->modificationContext = mock(ModificationContextInterface::class);
        $this->newNodePrinter = mock(NewNodePrinterInterface::class);
        $this->node = mock(Node::class);
        $this->printedNode = mock(PrintedNodeInterface::class);

        $this->printer = new SingleNodePrinter($this->existingNodePrinter, $this->newNodePrinter);
    }

    public function testPrintNodePrintsExistingNodeThroughExistingNodePrinter(): void
    {
        $this->node->allows()
            ->getStartFilePos()
            ->andReturn(101);
        $this->existingNodePrinter->allows()
            ->printNode($this->node, 21, $this->modificationContext)
            ->andReturn($this->printedNode);

        static::assertSame(
            $this->printedNode,
            $this->printer->printNode($this->node, 21, $this->modificationContext)
        );
    }

    public function testPrintNodePrintsNewNodeThroughNewNodePrinter(): void
    {
        $this->node->allows()
            ->getStartFilePos()
            ->andReturn(-1);
        $this->newNodePrinter->allows()
            ->printNode($this->node, 27, $this->modificationContext)
            ->andReturn($this->printedNode);

        static::assertSame(
            $this->printedNode,
            $this->printer->printNode($this->node, 27, $this->modificationContext)
        );
    }
}
