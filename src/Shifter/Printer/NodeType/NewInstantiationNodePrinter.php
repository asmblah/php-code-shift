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
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\New_;

/**
 * Class NewInstantiationNodePrinter.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class NewInstantiationNodePrinter implements NodeTypePrinterInterface
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
        return New_::class;
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
        New_ $node,
        int $line,
        ModificationContextInterface $modificationContext
    ): PrintedNodeInterface {
        $currentLine = $line;

        $printedClassNode = $this->nodePrinter->printNode($node->class, $currentLine, $modificationContext);
        $currentLine = $printedClassNode->getEndLine();
        $printedArgsNodeCollection = $this->nodePrinter->printNodeCollection(
            $node->args,
            $currentLine,
            $modificationContext
        );
        $currentLine = $printedArgsNodeCollection->getEndLine();

        $printedClass = $printedClassNode->getCode();

        if ($node->class instanceof Expr) {
            // Class is a complex expression, surround with parentheses to avoid a syntax error.
            $printedClass = '(' . $printedClass . ')';
        }

        $replacementCode = 'new ' . $printedClass . '(' . $printedArgsNodeCollection->join(', ') . ')';

        return new PrintedNode($replacementCode, $line, $currentLine);
    }
}
