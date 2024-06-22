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

namespace Asmblah\PhpCodeShift\Shifter\Resolver;

use PhpParser\Node;

/**
 * Interface NodeResolverInterface.
 *
 * Extracts the original AST node that a given node replaces, if any.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface NodeResolverInterface
{
    /**
     * Extracts and returns the replaced node, or null if there was none.
     */
    public function extractReplacedNode(Node $node): ?Node;
}
