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
use PhpParser\Node;

/**
 * Interface NodeCollectionPrinterInterface.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface NodeCollectionPrinterInterface
{
    /**
     * Prints a collection of AST nodes, preserving formatting
     * and guaranteeing line numbers do not change wherever possible.
     *
     * @param Node[] $nodes
     */
    public function printNodeCollection(
        array $nodes,
        int $line,
        ModificationContextInterface $modificationContext
    ): PrintedNodeCollectionInterface;
}
