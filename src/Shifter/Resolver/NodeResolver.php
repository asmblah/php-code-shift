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

use Asmblah\PhpCodeShift\Shifter\Ast\NodeAttribute;
use PhpParser\Node;

/**
 * Class NodeResolver.
 *
 * Extracts the original AST node that a given node replaces, if any.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class NodeResolver implements NodeResolverInterface
{
    /**
     * @inheritDoc
     */
    public function extractReplacedNode(Node $node): ?Node
    {
        /** @var Node|null $replacedNode */
        $replacedNode = $node->getAttribute(NodeAttribute::REPLACED_NODE);

        if ($replacedNode === null) {
            // Early-out; node is not a replacement for an earlier one.
            return null;
        }

        while ($replacedNode->hasAttribute(NodeAttribute::REPLACED_NODE)) {
            $replacedNode = $replacedNode->getAttribute(NodeAttribute::REPLACED_NODE);
        }

        if (
            !$replacedNode->hasAttribute(NodeAttribute::START_FILE_POS) ||
            !$replacedNode->hasAttribute(NodeAttribute::END_FILE_POS)
        ) {
            // Node is missing location information, therefore it is not an original AST node.
            return null;
        }

        return $replacedNode;
    }
}
