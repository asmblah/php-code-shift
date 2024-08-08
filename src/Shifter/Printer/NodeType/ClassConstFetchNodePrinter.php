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
use PhpParser\Node\Expr\ClassConstFetch;

/**
 * Class ClassConstFetchNodePrinter.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ClassConstFetchNodePrinter implements NodeTypePrinterInterface
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
        return ClassConstFetch::class;
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
        ClassConstFetch $node,
        int $line,
        ModificationContextInterface $modificationContext
    ): PrintedNodeInterface {
        $currentLine = $line;

        $printedClassNode = $this->nodePrinter->printNode($node->class, $currentLine, $modificationContext);
        $currentLine = $printedClassNode->getEndLine();
        $printedNameNode = $this->nodePrinter->printNode($node->name, $currentLine, $modificationContext);
        $currentLine = $printedNameNode->getEndLine();

        $printedClass = $printedClassNode->getCode();

        if ($node->class instanceof Expr) {
            // Class is a complex expression, surround with parentheses to avoid a syntax error.
            $printedClass = '(' . $printedClass . ')';
        }

        $printedName = $printedNameNode->getCode();

        if ($node->name instanceof Expr) {
            // Name is a complex expression, surround with braces to avoid a syntax error.
            $printedName = '{' . $printedName . '}';
        }

        $replacementCode = $printedClass . '::' . $printedName;

        return new PrintedNode($replacementCode, $line, $currentLine);
    }
}
