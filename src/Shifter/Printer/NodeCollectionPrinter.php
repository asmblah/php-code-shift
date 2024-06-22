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

use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Code\Context\ModificationContextInterface;

/**
 * Class NodeCollectionPrinter.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class NodeCollectionPrinter implements NodeCollectionPrinterInterface
{
    public function __construct(
        private readonly SingleNodePrinterInterface $singleNodePrinter
    ) {
    }

    /**
     * @inheritDoc
     */
    public function printNodeCollection(
        array $nodes,
        int $line,
        ModificationContextInterface $modificationContext
    ): PrintedNodeCollectionInterface {
        $currentLine = $line;
        $printedNodes = [];

        foreach ($nodes as $node) {
            $printedNode = $this->singleNodePrinter->printNode($node, $currentLine, $modificationContext);
            $currentLine = $printedNode->getEndLine();

            $printedNodes[] = $printedNode;
        }

        return new PrintedNodeCollection($printedNodes, $line, $currentLine);
    }
}
