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

/**
 * Interface NodePrinterInterface.
 *
 * Facade for printing AST nodes.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface NodePrinterInterface extends NodeCollectionPrinterInterface, SingleNodePrinterInterface
{
}
