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
use PhpParser\Node\Name\FullyQualified;

/**
 * Class FullyQualifiedNameNodePrinter.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class FullyQualifiedNameNodePrinter implements NodeTypePrinterInterface
{
    /**
     * @inheritDoc
     */
    public function getNodeClassName(): string
    {
        return FullyQualified::class;
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
        FullyQualified $node,
        int $line
    ): PrintedNodeInterface {
        $replacementCode = $node->toCodeString();

        return new PrintedNode($replacementCode, $line, $line + substr_count($replacementCode, PHP_EOL));
    }
}
