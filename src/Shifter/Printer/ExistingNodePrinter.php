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
use Asmblah\PhpCodeShift\Shifter\Shift\Context\ModificationContextInterface;
use LogicException;
use PhpParser\Node;

/**
 * Class ExistingNodePrinter.
 *
 * Extracts the source code of nodes that already existed in the AST.
 * Usually applies where a node has been moved/nested inside a new one.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ExistingNodePrinter implements ExistingNodePrinterInterface
{
    /**
     * @inheritDoc
     */
    public function printNode(
        Node $node,
        int $line,
        ModificationContextInterface $modificationContext
    ): PrintedNodeInterface {
        // AST node already has position information,
        // so we can extract it verbatim from the working contents.
        $start = $node->getAttribute(NodeAttribute::START_FILE_POS);
        $length = $node->getAttribute(NodeAttribute::END_FILE_POS) - $start + 1;
        $startLine = $node->getAttribute(NodeAttribute::START_LINE);
        $endLine = $node->getAttribute(NodeAttribute::END_LINE);

        $replacementCode = substr($modificationContext->getContents(), $start + $modificationContext->getDelta(), $length);

        if ($line > $startLine) {
            throw new LogicException(
                sprintf(
                    'Impossible to match original line of %d when current line of %d is beyond it',
                    $startLine,
                    $line
                )
            );
        }

        $lineDiscrepancy = $startLine - $line;

        // Pad out the returned code with leading newlines to reach the original line.
        $padding = str_repeat("\n", $lineDiscrepancy);

        return new PrintedNode($padding . $replacementCode, $line + $lineDiscrepancy, $endLine);
    }
}
