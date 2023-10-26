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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter\Shift\Modification\Code;

use Asmblah\PhpCodeShift\Shifter\Printer\NodePrinterInterface;
use Asmblah\PhpCodeShift\Shifter\Printer\PrintedNodeInterface;
use Asmblah\PhpCodeShift\Shifter\Resolver\CodeModificationExtentsInterface;
use Asmblah\PhpCodeShift\Shifter\Resolver\ExtentResolverInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Code\Context\ModificationContextInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Code\ModificationVisitor;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use LogicException;
use Mockery;
use Mockery\MockInterface;
use PhpParser\Node;
use PhpParser\NodeTraverser;

/**
 * Class ModificationVisitorTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ModificationVisitorTest extends AbstractTestCase
{
    private MockInterface&ExtentResolverInterface $extentResolver;
    private MockInterface&ModificationContextInterface $modificationContext;
    private MockInterface&CodeModificationExtentsInterface $modificationExtents;
    private MockInterface&Node $node;
    private MockInterface&NodePrinterInterface $nodePrinter;
    private MockInterface&PrintedNodeInterface $printedNode;
    private ModificationVisitor $visitor;

    public function setUp(): void
    {
        $this->extentResolver = mock(ExtentResolverInterface::class);
        $this->modificationContext = mock(ModificationContextInterface::class, [
            'getContents' => 'here we see my original code the end',
            'getDelta' => 4,
        ]);
        $this->modificationExtents = mock(CodeModificationExtentsInterface::class, [
            'getStartLine' => 2,
            'getStartOffset' => 12 - 4, // Adjust by delta set in context above.
            'getEndLine' => 5,
            'getEndOffset' => 28 - 4, // As above.
        ]);
        $this->node = mock(Node::class);
        $this->printedNode = mock(PrintedNodeInterface::class, [
            'getStartLine' => 2,
            'getEndLine' => 5,
            'getCode' => 'my newly printed code',
        ]);
        $this->nodePrinter = mock(NodePrinterInterface::class, [
            'printNode' => $this->printedNode,
        ]);

        $this->extentResolver->allows()
            ->resolveModificationExtents($this->node, $this->modificationContext)
            ->andReturn($this->modificationExtents)
            ->byDefault();
        $this->modificationContext->allows()
            ->setDelta(Mockery::any())
            ->andReturnUsing(function (int $delta) {
                $this->modificationContext->allows()
                    ->getDelta()
                    ->andReturn($delta)
                    ->byDefault();
            })
            ->byDefault();
        $this->modificationContext->allows()
            ->setContents(Mockery::any())
            ->andReturnUsing(function (string $contents) {
                $this->modificationContext->allows()
                    ->getContents()
                    ->andReturn($contents)
                    ->byDefault();
            })
            ->byDefault();

        $this->visitor = new ModificationVisitor($this->modificationContext, $this->extentResolver, $this->nodePrinter);
    }

    public function testEnterNodeReturnsNullWhenNoExtentsResolvedForNode(): void
    {
        $this->extentResolver->allows()
            ->resolveModificationExtents($this->node, $this->modificationContext)
            ->andReturnNull();

        static::assertNull($this->visitor->enterNode($this->node));
    }

    public function testEnterNodeCorrectlyHandlesValidExtentsBeingResolvedForNode(): void
    {
        $this->nodePrinter->allows()
            ->printNode($this->node, 2, $this->modificationContext)
            ->andReturn($this->printedNode);

        static::assertSame(
            NodeTraverser::DONT_TRAVERSE_CHILDREN,
            $this->visitor->enterNode($this->node)
        );
        static::assertSame('here we see my newly printed code the end', $this->modificationContext->getContents());
        static::assertSame(
            9,
            $this->modificationContext->getDelta(),
            'Delta should be original of 4 plus difference between old and new node code'
        );
    }

    public function testEnterNodeRaisesExceptionWhenPrintedStartLineDoesNotMatch(): void
    {
        $this->printedNode->allows()
            ->getStartLine()
            ->andReturn(3);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Modification result\'s start line must be 2 but it will be 3');

        $this->visitor->enterNode($this->node);
    }

    public function testEnterNodeRaisesExceptionWhenPrintedEndLineDoesNotMatch(): void
    {
        $this->printedNode->allows()
            ->getEndLine()
            ->andReturn(6);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Modification result\'s end line must be 5 but it will be 6');

        $this->visitor->enterNode($this->node);
    }
}
