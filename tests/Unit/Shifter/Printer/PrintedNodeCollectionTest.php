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

use Asmblah\PhpCodeShift\Shifter\Printer\PrintedNodeCollection;
use Asmblah\PhpCodeShift\Shifter\Printer\PrintedNodeInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery\MockInterface;

/**
 * Class PrintedNodeCollectionTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class PrintedNodeCollectionTest extends AbstractTestCase
{
    private PrintedNodeCollection $collection;
    private MockInterface&PrintedNodeInterface $printedNode1;
    private MockInterface&PrintedNodeInterface $printedNode2;

    public function setUp(): void
    {
        $this->printedNode1 = mock(PrintedNodeInterface::class, [
            'getCode' => '"my first node"',
        ]);
        $this->printedNode2 = mock(PrintedNodeInterface::class, [
            'getCode' => '"my second node"',
        ]);

        $this->collection = new PrintedNodeCollection([$this->printedNode1, $this->printedNode2], 21, 101);
    }

    public function testGetEndLineReturnsTheEndLine(): void
    {
        static::assertSame(101, $this->collection->getEndLine());
    }

    public function testGetPrintedNodesReturnsTheNodesInCollection(): void
    {
        static::assertSame(
            [$this->printedNode1, $this->printedNode2],
            $this->collection->getPrintedNodes()
        );
    }

    public function testGetStartLineReturnsTheStartLine(): void
    {
        static::assertSame(21, $this->collection->getStartLine());
    }

    public function testJoinCorrectlyJoinsTheCodeOfTheNodesInCollection(): void
    {
        static::assertSame(
            '"my first node","my second node"',
            $this->collection->join(',')
        );
    }
}
