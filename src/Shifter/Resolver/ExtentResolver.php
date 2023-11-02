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
use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Code\Context\ModificationContextInterface;
use LogicException;
use PhpParser\Node;
use PhpParser\Node\Stmt;

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
    public function resolveModificationExtents(
        Node $node,
        ModificationContextInterface $modificationContext
    ): ?CodeModificationExtentsInterface {
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
                $parentNode = $node->getAttribute(NodeAttribute::PARENT_NODE);

                if ($parentNode === null) {
                    throw new LogicException(
                        'Missing attribute ::PARENT_NODE for insertion type ::FIRST_CHILD'
                    );
                }

                if (!$parentNode instanceof Node) {
                    throw new LogicException('Parent node is not a valid AST node');
                }

                if (!property_exists($parentNode, 'stmts')) {
                    throw new LogicException('Parent node does not have child ->stmts');
                }

                /** @var Stmt|null $nextSibling */
                $nextSibling = null;

                foreach ($parentNode->stmts as $statementNode) {
                    if ($statementNode === $node) {
                        continue; // Ignore the new child node.
                    }

                    $nextSibling = $this->nodeResolver->extractReplacedNode($statementNode) ?? $statementNode;
                    break;
                }

                if ($nextSibling !== null) {
                    // Insert the new node just before its new next sibling.
                    $line = $nextSibling->getStartLine();
                    $offset = $nextSibling->getStartFilePos();

                    if ($line === -1 || $offset === -1) {
                        throw new LogicException('Cannot resolve sibling extents');
                    }
                } else {
                    // Otherwise there is no sibling to insert just before, so we need to find the closing brace
                    // of the parent block node.

                    $position = $parentNode->getEndFilePos();

                    if ($position === -1) {
                        throw new LogicException('Parent node is missing end file position');
                    }

                    /*
                     * There could be comments containing braces inside the empty parent node,
                     * so just find the closing brace rather than having to handle comments
                     * in order to find the opening brace.
                     */
                    $offset = strrpos(
                        $modificationContext->getContents(),
                        '}',

                        /*
                         * Use a negative offset so that the reverse search looks left of the computed offset.
                         * As the searched contents are the latest modified contents, delta must be applied
                         * to the starting offset of the search.
                         */
                        -(strlen($modificationContext->getContents()) - $position - $modificationContext->getDelta())
                    );

                    if ($offset === false) {
                        throw new LogicException('Cannot find closing brace of parent node');
                    }

                    /*
                     * Extents must be in terms of the original contents, and so delta is applied later on.
                     * For this reason, we need to remove the delta from the offset.
                     */
                    $offset -= $modificationContext->getDelta();

                    $line = $parentNode->getEndLine();
                }

                return new CodeModificationExtents($offset, $line, $offset, $line);
            case InsertionType::NONE:
                // Node is original, so there is no modification to make.
                return null;
            default:
                throw new LogicException(sprintf('Unknown insertion type "%s"', $insertionType));
        }
    }
}
