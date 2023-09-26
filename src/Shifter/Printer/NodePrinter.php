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

use Asmblah\PhpCodeShift\Shifter\Shift\Context\ModificationContextInterface;
use PhpParser\Node;

/**
 * Class NodePrinter.
 *
 * Facade for printing AST nodes.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class NodePrinter implements NodePrinterInterface
{
    private SingleNodePrinterInterface $singleNodePrinter;
    private NodeCollectionPrinterInterface $nodeCollectionPrinter;

    /**
     * @inheritDoc
     */
    public function printNode(
        Node $node,
        int $line,
        ModificationContextInterface $modificationContext
    ): PrintedNodeInterface {
        return $this->singleNodePrinter->printNode($node, $line, $modificationContext);
    }

    public function printNodeCollection(
        array $nodes,
        int $line,
        ModificationContextInterface $modificationContext
    ): PrintedNodeCollectionInterface {
        return $this->nodeCollectionPrinter->printNodeCollection($nodes, $line, $modificationContext);
    }

    /**
     * Injects the NodeCollectionPrinter dependency.
     */
    public function setNodeCollectionPrinter(NodeCollectionPrinterInterface $nodeCollectionPrinter): void
    {
        $this->nodeCollectionPrinter = $nodeCollectionPrinter;
    }

    /**
     * Injects the SingleNodePrinter dependency.
     */
    public function setSingleNodePrinter(SingleNodePrinterInterface $singleNodePrinter): void
    {
        $this->singleNodePrinter = $singleNodePrinter;
    }
}
