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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Shift\Tock;

use Asmblah\PhpCodeShift\Attribute\Tockless;
use Asmblah\PhpCodeShift\Shifter\Ast\NodeAttribute;
use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Ast\InsertAsFirstChildModification;
use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Ast\ModificationInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Traverser\Visitor\AbstractNodeVisitor;
use PhpParser\Node;

/**
 * Class TockSiteVisitor.
 *
 * Adds the tock statement node at tock sites (entry to userland functions/closures/methods
 * and the top of loop bodies).
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class TockSiteVisitor extends AbstractNodeVisitor
{
    public const TOCK_NODE_ATTRIBUTE = NodeAttribute::PREFIX . 'type_tock_applied';

    public function __construct(
        private readonly TockStatementShiftSpec $shiftSpec
    ) {
    }

    /**
     * Adds the tock statement node at tock sites.
     */
    public function enterNode(Node $node): ?ModificationInterface
    {
        if ($node->getAttribute(self::TOCK_NODE_ATTRIBUTE, false) === true) {
            // This shift was already applied.
            return null;
        }

        // Don't apply to function-likes that have the attribute Tockless.
        if ($node instanceof Node\FunctionLike) {
            foreach ($node->getAttrGroups() as $attributeGroup) {
                foreach ($attributeGroup->attrs as $attribute) {
                    if ($attribute->name->toLowerString() === strtolower(Tockless::class)) {
                        return null; // Leave the node unchanged.
                    }
                }
            }
        }

        if (
            // Loop structures.
            $node instanceof Node\Stmt\Do_||
            $node instanceof Node\Stmt\For_ ||
            $node instanceof Node\Stmt\Foreach_ ||
            $node instanceof Node\Stmt\While_ ||

            // Function-likes.
            $node instanceof Node\Expr\Closure ||
            $node instanceof Node\Stmt\Function_ ||
            ($node instanceof Node\Stmt\ClassMethod && $node->stmts !== null) // Exclude abstract or interface methods.
        ) {
            $replacementNode = clone $node;
            $tockStatementNode = $this->shiftSpec->createStatementNode();

            array_unshift($replacementNode->stmts, $tockStatementNode);

            // Set the attribute indicating that this shift was applied.
            $replacementNode->setAttribute(self::TOCK_NODE_ATTRIBUTE, true);

            // Only the tock statement added is new, so use a InsertBeforeModification
            // to indicate that we do not want to re-print the node itself.
            return new InsertAsFirstChildModification(
                $node,
                $replacementNode,
                $tockStatementNode
            );
        }

        return null; // Leave the node unchanged.
    }
}
