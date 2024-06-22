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

use Asmblah\PhpCodeShift\Shifter\Printer\NodeType\EncapsedStringPartNodePrinter;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use PhpParser\Node\Scalar\EncapsedStringPart;

/**
 * Class EncapsedStringPartNodePrinterTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class EncapsedStringPartNodePrinterTest extends AbstractTestCase
{
    private EncapsedStringPart $node;
    private EncapsedStringPartNodePrinter $printer;

    public function setUp(): void
    {
        $this->node = new EncapsedStringPart("my \nstring \npart is here");
        $this->printer = new EncapsedStringPartNodePrinter();
    }

    public function testGetNodeClassNameReturnsCorrectClass(): void
    {
        static::assertSame(EncapsedStringPart::class, $this->printer->getNodeClassName());
    }

    public function testPrintNodeAddsNewlinesInCodeToEndLine(): void
    {
        $printedNode = $this->printer->printNode($this->node, 21);

        static::assertSame("my \nstring \npart is here", $printedNode->getCode());
        static::assertSame(21, $printedNode->getStartLine());
        static::assertSame(23, $printedNode->getEndLine());
    }
}
