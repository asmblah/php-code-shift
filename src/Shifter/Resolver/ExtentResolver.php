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

use Asmblah\PhpCodeShift\Shifter\Ast\InsertionType;
use Asmblah\PhpCodeShift\Shifter\Ast\NodeAttribute;
use LogicException;
use PhpParser\Node;

/**
 * Class ExtentResolver.
 *
 * Resolves the extents of code modifications depending on whether the AST node is original,
 * a replacement for an original node or an entirely new one.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ExtentResolver implements ExtentResolverInterface
{
    public function __construct(
        private readonly NodeResolverInterface $nodeResolver
    ) {
    }

    /**
     * @inheritDoc
     */
    public function resolveModificationExtents(Node $node): ?CodeModificationExtentsInterface
    {
        if ($node->getAttribute(NodeAttribute::TRAVERSE_INSIDE, false)) {
            // Early-out; node was replaced but is to be traversed inside.
            // This is used to keep AST nodes immutable while allowing descendants to be changed.
            return null;
        }

        $replacedNode = $this->nodeResolver->extractReplacedNode($node);

        if ($replacedNode !== null) {
            // Node replaces an original one.
            return new CodeModificationExtents(
                $replacedNode->getStartFilePos(),
                $replacedNode->getStartLine(),
                $replacedNode->getEndFilePos() + 1,
                $replacedNode->getEndLine()
            );
        }

        $insertionType = $node->getAttribute(NodeAttribute::INSERTION_TYPE, InsertionType::NONE);

        switch ($insertionType) {
            case InsertionType::AFTER_NODE:
                throw new LogicException('Insertion type ::AFTER_NODE not yet supported');
            case InsertionType::BEFORE_NODE:
                $nextSibling = $node->getAttribute(NodeAttribute::NEXT_SIBLING);

                if ($nextSibling === null) {
                    throw new LogicException(
                        'Missing attribute ::NEXT_SIBLING for insertion type ::BEFORE_NODE'
                    );
                }

                // Sibling may be a replacement for an original AST node.
                $nextSibling = $this->nodeResolver->extractReplacedNode($nextSibling) ?? $nextSibling;

                // Insert the new node just before its new next sibling.
                $line = $nextSibling->getStartLine();
                $offset = $nextSibling->getStartFilePos();

                return new CodeModificationExtents($offset, $line, $offset, $line);
            case InsertionType::FIRST_CHILD:
                throw new LogicException('Insertion type ::FIRST_CHILD not yet supported');
            case InsertionType::NONE:
                // Node is original, so there is no modification to make.
                return null;
            default:
                throw new LogicException(sprintf('Unknown insertion type "%s"', $insertionType));
        }
    }
}
