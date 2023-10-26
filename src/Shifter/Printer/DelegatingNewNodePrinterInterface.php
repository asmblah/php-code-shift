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

/**
 * Interface DelegatingNewNodePrinterInterface.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface DelegatingNewNodePrinterInterface extends NewNodePrinterInterface
{
    /**
     * Registers a printer for nodes of a certain type.
     */
    public function registerNodePrinter(NodeTypePrinterInterface $nodeTypePrinter): void;
}
