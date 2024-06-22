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

use Asmblah\PhpCodeShift\Shifter\Printer\NodeType\IdentifierNodePrinter;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use PhpParser\Node\Identifier;

/**
 * Class IdentifierNodePrinterTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class IdentifierNodePrinterTest extends AbstractTestCase
{
    private Identifier $node;
    private IdentifierNodePrinter $printer;

    public function setUp(): void
    {
        $this->node = new Identifier("my\nidentifier\nhere");
        $this->printer = new IdentifierNodePrinter();
    }

    public function testGetNodeClassNameReturnsCorrectClass(): void
    {
        static::assertSame(Identifier::class, $this->printer->getNodeClassName());
    }

    public function testPrintNodeAddsNewlinesInCodeToEndLine(): void
    {
        $printedNode = $this->printer->printNode($this->node, 21);

        static::assertSame("my\nidentifier\nhere", $printedNode->getCode());
        static::assertSame(21, $printedNode->getStartLine());
        static::assertSame(23, $printedNode->getEndLine());
    }
}
