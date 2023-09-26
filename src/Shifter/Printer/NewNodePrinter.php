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

namespace Asmblah\PhpCodeShift\Shifter\Printer;

use Asmblah\PhpCodeShift\Shifter\Ast\NodeAttribute;
use Asmblah\PhpCodeShift\Shifter\Resolver\NodeResolverInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Context\ModificationContextInterface;
use LogicException;
use PhpParser\Node;

/**
 * Class NewNodePrinter.
 *
 * Generates code for nodes that did not previously exist in the AST at all.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class NewNodePrinter implements NewNodePrinterInterface
{
    public function __construct(
        private readonly NodeResolverInterface $nodeResolver,
        private readonly DelegatingNewNodePrinterInterface $delegatingNodePrinter
    ) {
    }

    /**
     * @inheritDoc
     */
    public function printNode(
        Node $node,
        int $line,
        ModificationContextInterface $modificationContext
    ): PrintedNodeInterface {
        $printedNode = $this->delegatingNodePrinter->printNode($node, $line, $modificationContext);

        // Now look for a node that this AST node replaced; if there was one,
        // then we must adjust the code output for this new node to maintain the original line number.
        $replacedNode = $this->nodeResolver->extractReplacedNode($node);

        if ($replacedNode !== null) {
            $startLine = $replacedNode->getAttribute(NodeAttribute::START_LINE);
            $endLine = $replacedNode->getAttribute(NodeAttribute::END_LINE);

            $currentLine = $printedNode->getEndLine();
            $replacementCode = $printedNode->getCode();

            if ($line > $startLine) {
                throw new LogicException(
                    sprintf(
                        'Impossible to match original start line of %d when current line of %d is beyond it',
                        $startLine,
                        $line
                    )
                );
            }

            if ($currentLine > $endLine) {
                throw new LogicException(
                    sprintf(
                        'Impossible to match original end line of %d when exit line of %d is beyond it',
                        $endLine,
                        $currentLine
                    )
                );
            }

            if ($line !== $startLine || $currentLine !== $endLine) {
                // Node needs to be adjusted to preserve line numbers.

                $leadingLineDiscrepancy = $startLine - $line;
                $leadingPadding = str_repeat("\n", $leadingLineDiscrepancy);

                $trailingLineDiscrepancy = $endLine - $currentLine;
                $trailingPadding = str_repeat("\n", $trailingLineDiscrepancy);

                $replacementCode = $leadingPadding . $replacementCode . $trailingPadding;
                $currentLine = $endLine; // Now that we've padded ourselves out to the correct end line.

                // Create a new printed node that has been adjusted to align it with the replaced node's line.
                $printedNode = new PrintedNode($replacementCode, $line, $currentLine);
            }
        }

        return $printedNode;
    }
}
