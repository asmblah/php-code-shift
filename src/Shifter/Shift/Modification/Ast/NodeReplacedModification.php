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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Modification\Ast;

use Asmblah\PhpCodeShift\Shifter\Ast\NodeAttribute;
use PhpParser\Node;

/**
 * Class NodeReplacedModification.
 *
 * Indicates that the new node replaces the old one entirely and so it should be printed
 * and the result used to replace the entire code of the original node.
 * No descendants will be traversed into as the entire node replacement
 * is expected to include every change required.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class NodeReplacedModification implements ModificationInterface
{
    public function __construct(
        private readonly Node $originalNode,
        private readonly Node $replacementNode
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getLibraryResult(): mixed
    {
        // Add a pointer from the original node to its replacement.
        $this->originalNode->setAttribute(NodeAttribute::REPLACEMENT_NODE, $this->replacementNode);

        $this->replacementNode->setAttributes([
            // Ensure we preserve any other attributes, which may include
            // flags that indicate a shift was applied to prevent infinite processing.
            ...$this->replacementNode->getAttributes(),

            // Add a pointer back from the new, replacement node back to the original one it replaces.
            NodeAttribute::REPLACED_NODE => $this->originalNode,

            // Indicate that it is the entire node that is to be replaced.
            NodeAttribute::TRAVERSE_INSIDE => false,

            // Clear the position attributes as this clone is not an existing node,
            // and some or all of these may be incorrect after the modification is applied.
            NodeAttribute::START_FILE_POS => null,
            NodeAttribute::START_LINE => null,
            NodeAttribute::END_FILE_POS => null,
            NodeAttribute::END_LINE => null,
        ]);

        return $this->replacementNode;
    }
}
