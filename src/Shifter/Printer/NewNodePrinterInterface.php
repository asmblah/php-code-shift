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
 * Interface NewNodePrinterInterface.
 *
 * Generates code for nodes that did not previously exist in the AST at all.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface NewNodePrinterInterface
{
    /**
     * Prints an AST node, preserving formatting and guaranteeing line numbers do not change wherever possible.
     */
    public function printNode(
        Node $node,
        int $line,
        ModificationContextInterface $modificationContext
    ): PrintedNodeInterface;
}
