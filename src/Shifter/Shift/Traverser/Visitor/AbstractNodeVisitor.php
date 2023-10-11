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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Traverser\Visitor;

use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Ast\ModificationInterface;
use PhpParser\Node;

/**
 * Class AbstractNodeVisitor.
 *
 * Handles processing of AST nodes.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class AbstractNodeVisitor implements NodeVisitorInterface
{
    /**
     * @inheritDoc
     */
    public function enterNode(Node $node): ?ModificationInterface
    {
        return null; // No change is to be made by default.
    }
}
