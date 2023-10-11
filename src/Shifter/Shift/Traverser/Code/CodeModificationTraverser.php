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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Traverser\Code;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;

/**
 * Class CodeModificationTraverser.
 *
 * Traverses an AST to perform any necessary code modifications.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class CodeModificationTraverser implements CodeModificationTraverserInterface
{
    /**
     * @var NodeVisitor[]
     */
    private array $nodeVisitors = [];

    /**
     * @inheritDoc
     */
    public function addVisitor(NodeVisitor $nodeVisitor): void
    {
        $this->nodeVisitors[] = $nodeVisitor;
    }

    /**
     * @inheritDoc
     */
    public function traverse(array $nodes): array
    {
        // NodeTraversers cannot be reused due to their internal state.
        $nodeTraverser = new NodeTraverser();

        foreach ($this->nodeVisitors as $nodeVisitor) {
            $nodeTraverser->addVisitor($nodeVisitor);
        }

        return $nodeTraverser->traverse($nodes);
    }
}
