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

namespace Asmblah\PhpCodeShift\Shifter\Printer;

use Asmblah\PhpCodeShift\Shifter\Resolver\ExtentResolverInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Code\Context\ModificationContextInterface;
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
        private readonly ExtentResolverInterface $extentResolver,
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

        /*
         * Now look for the extents at which this modification should take place.
         *
         * If a node was replaced by this AST node, then we must adjust the code output
         * for this new node to maintain the original line number.
         */
        $modificationExtents = $this->extentResolver->resolveModificationExtents($node, $modificationContext);

        if ($modificationExtents !== null) {
            $startLine = $modificationExtents->getStartLine();
            $endLine = $modificationExtents->getEndLine();

            $currentLine = $printedNode->getEndLine();
            $modificationCode = $printedNode->getCode();

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

                $modificationCode = $leadingPadding . $modificationCode . $trailingPadding;
                $currentLine = $endLine; // Now that we've padded ourselves out to the correct end line.

                // Create a new printed node that has been adjusted to align it with the modification extent's line.
                $printedNode = new PrintedNode($modificationCode, $line, $currentLine);
            }
        }

        return $printedNode;
    }
}
