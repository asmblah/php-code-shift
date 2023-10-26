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

use Asmblah\PhpCodeShift\Shifter\Printer\NodeCollectionPrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\PrintedNodeInterface;
use Asmblah\PhpCodeShift\Shifter\Printer\SingleNodePrinterInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Code\Context\ModificationContextInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery\MockInterface;
use PhpParser\Node;

/**
 * Class NodeCollectionPrinterTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class NodeCollectionPrinterTest extends AbstractTestCase
{
    private MockInterface&ModificationContextInterface $modificationContext;
    private NodeCollectionPrinter $printer;
    private MockInterface&SingleNodePrinterInterface $singleNodePrinter;

    public function setUp(): void
    {
        $this->modificationContext = mock(ModificationContextInterface::class);
        $this->singleNodePrinter = mock(SingleNodePrinterInterface::class);

        $this->printer = new NodeCollectionPrinter($this->singleNodePrinter);
    }

    public function testPrintNodeCollectionReturnsACorrectPrintedNodeCollection(): void
    {
        $node1 = mock(Node::class);
        $printedNode1 = mock(PrintedNodeInterface::class, [
            'getEndLine' => 15,
        ]);
        $this->singleNodePrinter->allows()
            ->printNode($node1, 10, $this->modificationContext)
            ->andReturn($printedNode1);
        $node2 = mock(Node::class);
        $printedNode2 = mock(PrintedNodeInterface::class, [
            'getEndLine' => 20,
        ]);
        $this->singleNodePrinter->allows()
            ->printNode($node2, 15, $this->modificationContext)
            ->andReturn($printedNode2);

        $nodeCollection = $this->printer->printNodeCollection([$node1, $node2], 10, $this->modificationContext);
        $printedNodes = $nodeCollection->getPrintedNodes();

        static::assertCount(2, $printedNodes);
        static::assertSame($printedNode1, $printedNodes[0]);
        static::assertSame($printedNode2, $printedNodes[1]);
        static::assertSame(
            10,
            $nodeCollection->getStartLine(),
            'Start line should be the initial line'
        );
        static::assertSame(
            20,
            $nodeCollection->getEndLine(),
            'End line should be that of final printed node'
        );
    }
}
