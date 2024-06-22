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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Traverser\Ast;

use Asmblah\PhpCodeShift\Shifter\Shift\Traverser\Visitor\NodeVisitorInterface;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\NameResolver;

/**
 * Class AstModificationTraverser.
 *
 * Traverses an AST to perform any necessary modifications to nodes of the AST itself.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class AstModificationTraverser implements AstModificationTraverserInterface
{
    /**
     * @var NodeVisitor[]
     */
    private array $nodeVisitors = [];

    /**
     * @inheritDoc
     */
    public function addLibraryVisitor(NodeVisitor $nodeVisitor): void
    {
        $this->nodeVisitors[] =  $nodeVisitor;
    }

    /**
     * @inheritDoc
     */
    public function addVisitor(NodeVisitorInterface $nodeVisitor): void
    {
        $this->nodeVisitors[] = new LibraryVisitor($nodeVisitor);
    }

    /**
     * @inheritDoc
     */
    public function traverse(array $nodes): array
    {
        // NodeTraversers cannot be reused due to their internal state.
        $nodeTraverser = new NodeTraverser();

        // Resolve names (e.g. class identifiers -> FQCNs).
        $nodeTraverser->addVisitor(new NameResolver());

        foreach ($this->nodeVisitors as $nodeVisitor) {
            $nodeTraverser->addVisitor($nodeVisitor);
        }

        return $nodeTraverser->traverse($nodes);
    }
}
