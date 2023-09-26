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
 * Interface AstTraverserInterface.
 *
 * Traverses an AST to perform any necessary modifications.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface AstTraverserInterface
{
    /**
     * Adds a visitor for AST nodes.
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
