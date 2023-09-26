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

use Asmblah\PhpCodeShift\Shifter\Ast\NodeAttribute;
use PhpParser\Node;
use PhpParser\NodeVisitor;

/**
 * Class LibraryVisitor.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class LibraryVisitor implements NodeVisitor
{
    public function __construct(
        private readonly NodeVisitorInterface $visitor
    ) {
    }

    /**
     * @inheritDoc
     */
    public function afterTraverse(array $nodes)
    {
        return $this->visitor->afterTraverse($nodes);
    }

    /**
     * @inheritDoc
     */
    public function beforeTraverse(array $nodes)
    {
        return $this->visitor->beforeTraverse($nodes);
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        $result = $this->visitor->enterNode($node);

        if ($result !== $node && $result instanceof Node) {
            $node->setAttribute(NodeAttribute::REPLACEMENT_NODE, $result);
            $result->setAttribute(NodeAttribute::REPLACED_NODE, $node);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function leaveNode(Node $node)
    {
        return $this->visitor->leaveNode($node);
    }
}
