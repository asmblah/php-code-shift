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

use Asmblah\PhpCodeShift\Shifter\Printer\NodeType\NameNodePrinter;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use PhpParser\Node\Name;

/**
 * Class NameNodePrinterTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class NameNodePrinterTest extends AbstractTestCase
{
    private Name $node;
    private NameNodePrinter $printer;

    public function setUp(): void
    {
        $this->node = new Name("my\nname\nhere");
        $this->printer = new NameNodePrinter();
    }

    public function testGetNodeClassNameReturnsCorrectClass(): void
    {
        static::assertSame(Name::class, $this->printer->getNodeClassName());
    }

    public function testPrintNodeAddsNewlinesInCodeToEndLine(): void
    {
        $printedNode = $this->printer->printNode($this->node, 21);

        static::assertSame("my\nname\nhere", $printedNode->getCode());
        static::assertSame(21, $printedNode->getStartLine());
        static::assertSame(23, $printedNode->getEndLine());
    }
}
