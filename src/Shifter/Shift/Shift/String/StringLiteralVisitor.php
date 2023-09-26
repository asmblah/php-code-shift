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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Shift\String;

use Asmblah\PhpCodeShift\Shifter\Shift\Traverser\AbstractNodeVisitor;
use PhpParser\Node;

/**
 * Class StringLiteralVisitor.
 *
 * Transforms call AST nodes to apply the string literal find & replace logic.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StringLiteralVisitor extends AbstractNodeVisitor
{
    public function __construct(
        private readonly StringLiteralShiftSpec $shiftSpec
    ) {
    }

    /**
     * Performs a find & replace inside all string literals.
     */
    public function enterNode(Node $node)
    {
        if (
            $node instanceof Node\Scalar\String_ ||
            $node instanceof Node\Scalar\EncapsedStringPart
        ) {
            $replacedString = str_replace(
                $this->shiftSpec->getNeedle(),
                $this->shiftSpec->getReplacement(),
                $node->value
            );

            return new $node($replacedString);
        }

        return null; // Leave the node unchanged.
    }
}
