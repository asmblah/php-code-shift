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

namespace Asmblah\PhpCodeShift\Shifter\Printer\NodeType;

use Asmblah\PhpCodeShift\Shifter\Printer\NodeTypePrinterInterface;
use Asmblah\PhpCodeShift\Shifter\Printer\PrintedNode;
use Asmblah\PhpCodeShift\Shifter\Printer\PrintedNodeInterface;
use PhpParser\Node\Scalar\EncapsedStringPart;

/**
 * Class EncapsedStringPartNodePrinter.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class EncapsedStringPartNodePrinter implements NodeTypePrinterInterface
{
    /**
     * @inheritDoc
     */
    public function getNodeClassName(): string
    {
        return EncapsedStringPart::class;
    }

    /**
     * @inheritDoc
     */
    public function getPrinter(): callable
    {
        return $this->printNode(...);
    }

    /**
     * Prints the new AST node.
     */
    public function printNode(
        EncapsedStringPart $node,
        int $line
    ): PrintedNodeInterface {
        // Note that we do not enclose the string part in quotes.
        $replacementCode = $node->value;

        return new PrintedNode($replacementCode, $line, $line + substr_count($replacementCode, PHP_EOL));
    }
}
