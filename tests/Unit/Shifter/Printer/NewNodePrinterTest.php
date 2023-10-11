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

use Asmblah\PhpCodeShift\Shifter\Printer\DelegatingNewNodePrinterInterface;
use Asmblah\PhpCodeShift\Shifter\Printer\NewNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\PrintedNodeInterface;
use Asmblah\PhpCodeShift\Shifter\Resolver\CodeModificationExtentsInterface;
use Asmblah\PhpCodeShift\Shifter\Resolver\ExtentResolverInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Code\Context\ModificationContextInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use LogicException;
use Mockery\MockInterface;
use PhpParser\Node;

/**
 * Class NewNodePrinterTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class NewNodePrinterTest extends AbstractTestCase
{
    private MockInterface&DelegatingNewNodePrinterInterface $delegatingNodePrinter;
    private MockInterface&ExtentResolverInterface $extentResolver;
    private MockInterface&ModificationContextInterface $modificationContext;
    private MockInterface&Node $node;
    private MockInterface&PrintedNodeInterface $printedNode;
    private NewNodePrinter $printer;

    public function setUp(): void
    {
        $this->printedNode = mock(PrintedNodeInterface::class, [
            'getCode' => "'my printed node'",
            'getEndLine' => 10,
        ]);
        $this->delegatingNodePrinter = mock(DelegatingNewNodePrinterInterface::class, [
            'printNode' => $this->printedNode,
        ]);
        $this->modificationContext = mock(ModificationContextInterface::class);
        $this->node = mock(Node::class);
        $this->extentResolver = mock(ExtentResolverInterface::class, [
            'resolveModificationExtents' => null,
        ]);

        $this->printer = new NewNodePrinter($this->extentResolver, $this->delegatingNodePrinter);
    }

    public function testPrintNodeInvokesTheDelegatorCorrectly(): void
    {
        $this->delegatingNodePrinter->expects()
            ->printNode($this->node, 21, $this->modificationContext)
            ->once()
            ->andReturn($this->printedNode);

        $this->printer->printNode($this->node, 21, $this->modificationContext);
    }

    public function testPrintNodeReturnsAdjustedPrintedNodeWhenReplacedNodeIsDefinedWithDifferentStartAndEndLines(): void
    {
        $modificationExtents = mock(CodeModificationExtentsInterface::class);
        $modificationExtents->allows()
            ->getStartLine()
            ->andReturn(10);
        $modificationExtents->allows()
            ->getEndLine()
            ->andReturn(15);
        $this->extentResolver->allows()
            ->resolveModificationExtents($this->node)
            ->andReturn($modificationExtents);

        $printedNode = $this->printer->printNode($this->node, 6, $this->modificationContext);

        static::assertNotSame($this->printedNode, $printedNode);
        static::assertSame(
            "\n\n\n\n'my printed node'\n\n\n\n\n",
            $printedNode->getCode(),
            'Padding should be leading 4 (10-6) and trailing 5 (15-10)'
        );
        static::assertSame(
            6,
            $printedNode->getStartLine(),
            'Start line should match current line, as this PrintedNode contains the padding'
        );
        static::assertSame(
            15,
            $printedNode->getEndLine(),
            'End line should match replaced node\'s end line'
        );
    }

    public function testPrintNodeReturnsAdjustedPrintedNodeWhenReplacedNodeIsDefinedWithDifferentStartLineOnly(): void
    {
        $modificationExtents = mock(CodeModificationExtentsInterface::class);
        $modificationExtents->allows()
            ->getStartLine()
            ->andReturn(8);
        $modificationExtents->allows()
            ->getEndLine()
            ->andReturn(10);
        $this->extentResolver->allows()
            ->resolveModificationExtents($this->node)
            ->andReturn($modificationExtents);

        $printedNode = $this->printer->printNode($this->node, 6, $this->modificationContext);

        static::assertNotSame($this->printedNode, $printedNode);
        static::assertSame(
            "\n\n'my printed node'",
            $printedNode->getCode(),
            'Padding should only be leading 2 (10-8)'
        );
        static::assertSame(
            6,
            $printedNode->getStartLine(),
            'Start line should match current line, as this PrintedNode contains the padding'
        );
        static::assertSame(
            10,
            $printedNode->getEndLine(),
            'End line should match replaced node\'s end line'
        );
    }

    public function testPrintNodeReturnsAdjustedPrintedNodeWhenReplacedNodeIsDefinedWithDifferentEndLineOnly(): void
    {
        $modificationExtents = mock(CodeModificationExtentsInterface::class);
        $modificationExtents->allows()
            ->getStartLine()
            ->andReturn(6);
        $modificationExtents->allows()
            ->getEndLine()
            ->andReturn(15);
        $this->extentResolver->allows()
            ->resolveModificationExtents($this->node)
            ->andReturn($modificationExtents);

        $printedNode = $this->printer->printNode($this->node, 6, $this->modificationContext);

        static::assertNotSame($this->printedNode, $printedNode);
        static::assertSame(
            "'my printed node'\n\n\n\n\n",
            $printedNode->getCode(),
            'Padding should only be trailing 5 (15-10)'
        );
        static::assertSame(
            6,
            $printedNode->getStartLine(),
            'Start line should match current line, as this PrintedNode contains the padding'
        );
        static::assertSame(
            15,
            $printedNode->getEndLine(),
            'End line should match replaced node\'s end line'
        );
    }

    public function testPrintNodeReturnsNodeFromDelegatorWhenReplacedNodeHadIdenticalStartAndEndLines(): void
    {
        $modificationExtents = mock(CodeModificationExtentsInterface::class);
        $modificationExtents->allows()
            ->getStartLine()
            ->andReturn(7);
        $modificationExtents->allows()
            ->getEndLine()
            ->andReturn(10);
        $this->extentResolver->allows()
            ->resolveModificationExtents($this->node)
            ->andReturn($modificationExtents);

        static::assertSame(
            $this->printedNode,
            $this->printer->printNode($this->node, 7, $this->modificationContext)
        );
    }

    public function testPrintNodeReturnsNodeFromDelegatorWhenNoReplacedNodeIsDefined(): void
    {
        static::assertSame(
            $this->printedNode,
            $this->printer->printNode($this->node, 21, $this->modificationContext)
        );
    }

    public function testPrintNodeRaisesExceptionWhenCurrentLineIsBeyondReplacedNodesStartLine(): void
    {
        $modificationExtents = mock(CodeModificationExtentsInterface::class);
        $modificationExtents->allows()
            ->getStartLine()
            ->andReturn(5);
        $modificationExtents->allows()
            ->getEndLine()
            ->andReturn(10);
        $this->extentResolver->allows()
            ->resolveModificationExtents($this->node)
            ->andReturn($modificationExtents);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Impossible to match original start line of 5 when current line of 6 is beyond it'
        );

        $this->printer->printNode($this->node, 6, $this->modificationContext);
    }

    public function testPrintNodeRaisesExceptionWhenExitLineIsBeyondReplacedNodesEndLine(): void
    {
        $modificationExtents = mock(CodeModificationExtentsInterface::class);
        $modificationExtents->allows()
            ->getStartLine()
            ->andReturn(6);
        $modificationExtents->allows()
            ->getEndLine()
            ->andReturn(9);
        $this->extentResolver->allows()
            ->resolveModificationExtents($this->node)
            ->andReturn($modificationExtents);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Impossible to match original end line of 9 when exit line of 10 is beyond it'
        );

        $this->printer->printNode($this->node, 6, $this->modificationContext);
    }
}
