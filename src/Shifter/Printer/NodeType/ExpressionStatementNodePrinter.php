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
use PhpParser\Node\Stmt\Expression;

/**
 * Class ExpressionStatementNodePrinter.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ExpressionStatementNodePrinter implements NodeTypePrinterInterface
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
        return Expression::class;
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
        Expression $node,
        int $line,
        ModificationContextInterface $modificationContext
    ): PrintedNodeInterface {
        $printedNode = $this->nodePrinter->printNode($node->expr, $line, $modificationContext);

        return new PrintedNode(
            $printedNode->getCode() . ';',
            $printedNode->getStartLine(),
            $printedNode->getEndLine()
        );
    }
}
