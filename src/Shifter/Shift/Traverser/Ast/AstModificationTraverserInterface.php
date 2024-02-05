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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Traverser\Ast;

use Asmblah\PhpCodeShift\Shifter\Shift\Traverser\Visitor\NodeVisitorInterface;
use PhpParser\Node;
use PhpParser\NodeVisitor;

/**
 * Interface AstModificationTraverserInterface.
 *
 * Traverses an AST to perform any necessary modifications to nodes of the AST itself.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface AstModificationTraverserInterface
{
    /**
     * Adds a visitor for AST nodes using the PHP Parser interface.
     */
    public function addLibraryVisitor(NodeVisitor $nodeVisitor): void;

    /**
     * Adds a visitor for AST nodes using PHP Code Shift's interface.
     */
    public function addVisitor(NodeVisitorInterface $nodeVisitor): void;

    /**
     * Traverses the given AST.
     *
     * @param Node[] $nodes
     * @return Node[]
     */
    public function traverse(array $nodes): array;
}
