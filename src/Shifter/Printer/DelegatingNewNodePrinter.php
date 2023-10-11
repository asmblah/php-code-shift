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
use InvalidArgumentException;
use PhpParser\Node;

/**
 * Class DelegatingNewNodePrinter.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class DelegatingNewNodePrinter implements DelegatingNewNodePrinterInterface
{
    /**
     * @var array<string, callable>
     */
    private array $nodeClassNameToPrinter = [];

    /**
     * @inheritDoc
     */
    public function printNode(
        Node $node,
        int $line,
        ModificationContextInterface $modificationContext
    ): PrintedNodeInterface {
        $callable = $this->nodeClassNameToPrinter[$node::class] ?? null;

        if ($callable === null) {
            throw new InvalidArgumentException(sprintf('Unsupported AST node type "%s"', $node::class));
        }

        return $callable($node, $line, $modificationContext);
    }

    /**
     * @inheritDoc
     */
    public function registerNodePrinter(NodeTypePrinterInterface $nodeTypePrinter): void
    {
        $this->nodeClassNameToPrinter[$nodeTypePrinter->getNodeClassName()] = $nodeTypePrinter->getPrinter();
    }
}
