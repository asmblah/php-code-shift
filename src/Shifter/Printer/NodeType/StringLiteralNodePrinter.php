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

namespace Asmblah\PhpCodeShift\Shifter\Printer\NodeType;

use Asmblah\PhpCodeShift\Shifter\Printer\NodeTypePrinterInterface;
use Asmblah\PhpCodeShift\Shifter\Printer\PrintedNode;
use Asmblah\PhpCodeShift\Shifter\Printer\PrintedNodeInterface;
use PhpParser\Node\Scalar\String_;

/**
 * Class StringLiteralNodePrinter.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StringLiteralNodePrinter implements NodeTypePrinterInterface
{
    /**
     * @inheritDoc
     */
    public function getNodeClassName(): string
    {
        return String_::class;
    }

    public function getPrinter(): callable
    {
        return $this->printNode(...);
    }

    /**
     * Prints the new AST node.
     */
    public function printNode(
        String_ $node,
        int $line
    ): PrintedNodeInterface {
        $replacementCode = var_export($node->value, true);

        return new PrintedNode($replacementCode, $line, $line);
    }
}
