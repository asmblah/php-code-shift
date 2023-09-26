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

use Asmblah\PhpCodeShift\Shifter\Printer\NodePrinterInterface;
use Asmblah\PhpCodeShift\Shifter\Printer\NodeTypePrinterInterface;
use Asmblah\PhpCodeShift\Shifter\Printer\PrintedNode;
use Asmblah\PhpCodeShift\Shifter\Printer\PrintedNodeInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Context\ModificationContextInterface;
use PhpParser\Node\Expr\StaticCall;

/**
 * Class StaticCallNodePrinter.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StaticCallNodePrinter implements NodeTypePrinterInterface
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
        return StaticCall::class;
    }

    public function getPrinter(): callable
    {
        return $this->printNode(...);
    }

    /**
     * Prints the new AST node.
     */
    public function printNode(
        StaticCall $node,
        int $line,
        ModificationContextInterface $modificationContext
    ): PrintedNodeInterface {
        $currentLine = $line;

        $printedClassNode = $this->nodePrinter->printNode($node->class, $currentLine, $modificationContext);
        $currentLine = $printedClassNode->getEndLine();
        $printedNameNode = $this->nodePrinter->printNode($node->name, $currentLine, $modificationContext);
        $currentLine = $printedNameNode->getEndLine();
        $printedArgsNodeCollection = $this->nodePrinter->printNodeCollection(
            $node->args,
            $currentLine,
            $modificationContext
        );
        $currentLine = $printedArgsNodeCollection->getEndLine();

        $replacementCode = $printedClassNode->getCode() . '::' .
            $printedNameNode->getCode() .
            '(' . $printedArgsNodeCollection->join(', ') . ')';

        return new PrintedNode($replacementCode, $line, $currentLine);
    }
}
