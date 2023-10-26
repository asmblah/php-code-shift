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

use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Code\Context\ModificationContextInterface;
use PhpParser\Node;

/**
 * Class SingleNodePrinter.
 *
 * Prints a single AST node, either an existing or new one, as applicable.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class SingleNodePrinter implements SingleNodePrinterInterface
{
    public function __construct(
        private readonly ExistingNodePrinterInterface $existingNodePrinter,
        private readonly NewNodePrinterInterface $newNodePrinter
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
        if ($node->getStartFilePos() > -1) {
            $printedReplacement = $this->existingNodePrinter->printNode($node, $line, $modificationContext);
        } else {
            $printedReplacement = $this->newNodePrinter->printNode($node, $line, $modificationContext);
        }

        return $printedReplacement;
    }
}
