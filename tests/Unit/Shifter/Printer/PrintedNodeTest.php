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

use Asmblah\PhpCodeShift\Shifter\Printer\PrintedNode;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;

/**
 * Class PrintedNodeTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class PrintedNodeTest extends AbstractTestCase
{
    private PrintedNode $node;

    public function setUp(): void
    {
        $this->node = new PrintedNode('"my node"', 21, 101);
    }

    public function testGetCodeReturnsTheNodesCode(): void
    {
        static::assertSame('"my node"', $this->node->getCode());
    }

    public function testGetEndLineReturnsTheEndLine(): void
    {
        static::assertSame(101, $this->node->getEndLine());
    }

    public function testGetStartLineReturnsTheStartLine(): void
    {
        static::assertSame(21, $this->node->getStartLine());
    }
}
