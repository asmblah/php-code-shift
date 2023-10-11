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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Modification\Ast;

use Asmblah\PhpCodeShift\Shifter\Ast\InsertionType;
use Asmblah\PhpCodeShift\Shifter\Ast\NodeAttribute;
use PhpParser\Node;

/**
 * Class InsertBeforeModification.
 *
 * Indicates that the new node is itself unchanged and therefore should not be printed,
 * a child node has been added that should be inserted before another sibling.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class InsertBeforeModification implements ModificationInterface
{
    public function __construct(
        private readonly Node $originalParentNode,
        private readonly Node $replacementParentNode,
        private readonly Node $newChildNode,
        private readonly Node $nextSibling
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getLibraryResult(): mixed
    {
        // Add a pointer from the original node to its replacement.
        $this->originalParentNode->setAttribute(NodeAttribute::REPLACEMENT_NODE, $this->replacementParentNode);

        $this->replacementParentNode->setAttributes([
            // Ensure we preserve any other attributes, which may include
            // flags that indicate a shift was applied to prevent infinite processing.
            ...$this->replacementParentNode->getAttributes(),

            // Add a pointer back from the new, replacement node back to the original one it replaces.
            NodeAttribute::REPLACED_NODE => $this->originalParentNode,

            // Indicate that it is the descendants of this node that should be replaced
            // and not this node itself.
            NodeAttribute::TRAVERSE_INSIDE => true,

            // Clear the position attributes as this clone is not an existing node,
            // and some or all of these may be incorrect after the modification is applied.
            NodeAttribute::START_FILE_POS => null,
            NodeAttribute::START_LINE => null,
            NodeAttribute::END_FILE_POS => null,
            NodeAttribute::END_LINE => null,
        ]);

        $this->newChildNode->setAttribute(NodeAttribute::INSERTION_TYPE, InsertionType::BEFORE_NODE);
        $this->newChildNode->setAttribute(NodeAttribute::NEXT_SIBLING, $this->nextSibling);

        return $this->replacementParentNode;
    }
}
