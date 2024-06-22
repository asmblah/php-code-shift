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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Traverser\Visitor;

use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Ast\ModificationInterface;
use PhpParser\Node;

/**
 * Interface NodeVisitorInterface.
 *
 * Handles processing of AST nodes.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface NodeVisitorInterface
{
    /**
     * Called upon entry of an AST node during traversal.
     *
     * Returns either an AST Modification with the AST change to make or null to indicate no change.
     */
    public function enterNode(Node $node): ?ModificationInterface;
}
