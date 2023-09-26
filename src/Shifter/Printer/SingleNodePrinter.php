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
use PhpParser\Node;

/**
 * Class SingleNodePrinter.
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
        if ($node->hasAttribute(NodeAttribute::START_FILE_POS)) {
            $printedReplacement = $this->existingNodePrinter->printNode($node, $line, $modificationContext);
        } else {
            $printedReplacement = $this->newNodePrinter->printNode($node, $line, $modificationContext);
        }

        return $printedReplacement;
    }
}
