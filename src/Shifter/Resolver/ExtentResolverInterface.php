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

namespace Asmblah\PhpCodeShift\Shifter\Resolver;

use PhpParser\Node;

/**
 * Interface ExtentResolverInterface.
 *
 * Resolves the extents of code modifications depending on whether the AST node is original,
 * a replacement for an original node or an entirely new one.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface ExtentResolverInterface
{
    /**
     * Resolves the extents for modification of this node:
     *
     * - If it is an original non-replaced node, null will be returned.
     * - If it is a replacement for an existing node, the extents of that original node will be returned.
     * - If it is an entirely new node, the extents of the slot it will be added at (with zero length)
     *   will be returned.
     */
    public function resolveModificationExtents(Node $node): ?CodeModificationExtentsInterface;
}
