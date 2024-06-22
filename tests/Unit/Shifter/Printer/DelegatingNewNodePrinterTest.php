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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter\Printer;

use Asmblah\PhpCodeShift\Shifter\Printer\DelegatingNewNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\NodeTypePrinterInterface;
use Asmblah\PhpCodeShift\Shifter\Printer\PrintedNodeInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Code\Context\ModificationContextInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use PhpParser\Node;

/**
 * Class DelegatingNewNodePrinterTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class DelegatingNewNodePrinterTest extends AbstractTestCase
{
    private MockInterface&ModificationContextInterface $modificationContext;
    private DelegatingNewNodePrinter $printer;

    public function setUp(): void
    {
        $this->modificationContext = mock(ModificationContextInterface::class);

        $this->printer = new DelegatingNewNodePrinter();
    }

    public function testPrintNodeCorrectlyInvokesCallableWhenAstNodeTypeSupported(): void
    {
        $node = mock(Node::class);
        $printerCallable = spy();
        $nodeTypePrinter = mock(NodeTypePrinterInterface::class, [
            'getNodeClassName' => $node::class,
            'getPrinter' => $printerCallable->__invoke(...),
        ]);
        $this->printer->registerNodePrinter($nodeTypePrinter);
        $printedNode = mock(PrintedNodeInterface::class);

        $printerCallable->expects()
            ->__invoke($node, 21, $this->modificationContext)
            ->once()
            ->andReturn($printedNode);

        $this->printer->printNode($node, 21, $this->modificationContext);
    }

    public function testPrintNodeReturnsPrintedNodeResultFromCallableWhenAstNodeTypeSupported(): void
    {
        $node = mock(Node::class);
        $printerCallable = spy();
        $nodeTypePrinter = mock(NodeTypePrinterInterface::class, [
            'getNodeClassName' => $node::class,
            'getPrinter' => $printerCallable->__invoke(...),
        ]);
        $this->printer->registerNodePrinter($nodeTypePrinter);
        $printedNode = mock(PrintedNodeInterface::class);
        $printerCallable->allows()
            ->__invoke(Mockery::andAnyOtherArgs())
            ->andReturn($printedNode);

        static::assertSame($printedNode, $this->printer->printNode($node, 21, $this->modificationContext));
    }

    public function testPrintNodeRaisesExceptionWhenAstNodeTypeUnsupported(): void
    {
        $node = mock(Node::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported AST node type "' . $node::class . '"');

        $this->printer->printNode($node, 21, $this->modificationContext);
    }
}
