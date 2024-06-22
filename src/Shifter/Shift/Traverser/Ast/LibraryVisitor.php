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
    public function afterTraverse(array $nodes): ?array
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function beforeTraverse(array $nodes): ?array
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        $result = $this->visitor->enterNode($node);

        if ($result === null) {
            // Early-out if no change is to be made.
            return null;
        }

        return $result->getLibraryResult();
    }

    /**
     * @inheritDoc
     */
    public function leaveNode(Node $node)
    {
        return null;
    }
}
