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

use Asmblah\PhpCodeShift\Shifter\Printer\NodePrinterInterface;
use Asmblah\PhpCodeShift\Shifter\Printer\NodeTypePrinterInterface;
use Asmblah\PhpCodeShift\Shifter\Printer\PrintedNode;
use Asmblah\PhpCodeShift\Shifter\Printer\PrintedNodeInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Code\Context\ModificationContextInterface;
use PhpParser\Node\Arg;

/**
 * Class ArgNodePrinter.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ArgNodePrinter implements NodeTypePrinterInterface
{
    public function __construct(
        private readonly NodePrinterInterface $nodePrinter
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getNodeClassName(): string
    {
        return Arg::class;
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
        Arg $node,
        int $line,
        ModificationContextInterface $modificationContext
    ): PrintedNodeInterface {
        $currentLine = $line;

        if ($node->name !== null) {
            $printedNameNode = $this->nodePrinter->printNode($node->name, $currentLine, $modificationContext);
            $currentLine = $printedNameNode->getEndLine();
        } else {
            $printedNameNode = null;
        }

        $printedValueNode = $this->nodePrinter->printNode($node->value, $currentLine, $modificationContext);
        $currentLine = $printedValueNode->getEndLine();

        $replacementCode = ($printedNameNode ? $printedNameNode->getCode() . ': ' : '') .
            $printedValueNode->getCode();

        return new PrintedNode($replacementCode, $line, $currentLine);
    }
}
