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

namespace Asmblah\PhpCodeShift\Shifter\Modifier;

use PhpParser\Node;

/**
 * Class AstNodeModification.
 *
 * Represents a code modification replacing an AST node with a raw code string.
 * This is done both for performance and to allow tighter control
 * over preservation of formatting.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class AstNodeModification implements ModificationInterface
{
    public function __construct(
        private readonly Node $node,
        private readonly string $replacement
    ) {
    }

    /**
     * @inheritDoc
     */
    public function perform(string $contents, ContextInterface $context): string
    {
        $start = $this->node->getAttribute('startFilePos');
        $length = $this->node->getAttribute('endFilePos') - $start + 1;

        $delta = $context->getDelta();

        // Apply the delta of this replacement to all subsequent ones.
        $context->setDelta($delta + (strlen($this->replacement) - $length));

        return substr_replace($contents, $this->replacement, $start + $delta, $length);
    }
}
