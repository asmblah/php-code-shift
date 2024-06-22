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

use PhpParser\Node;

/**
 * Interface NodeTypePrinterInterface.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface NodeTypePrinterInterface
{
    /**
     * Fetches the FQCN of AST node type that the printer supports.
     *
     * @return class-string<Node>
     */
    public function getNodeClassName(): string;

    /**
     * Fetches the callable that will print AST nodes of the supported type.
     */
    public function getPrinter(): callable;
}
