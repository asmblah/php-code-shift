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
 * Interface SingleNodePrinterInterface.
 *
 * Prints a single AST node, either an existing or new one, as applicable.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface SingleNodePrinterInterface
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
