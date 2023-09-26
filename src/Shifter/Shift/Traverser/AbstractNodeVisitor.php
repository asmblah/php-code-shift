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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Traverser;

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
    public function beforeTraverse(array $nodes)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function leaveNode(Node $node)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function afterTraverse(array $nodes)
    {
        return null;
    }
}
