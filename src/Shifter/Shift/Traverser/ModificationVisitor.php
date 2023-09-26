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

use Asmblah\PhpCodeShift\Shifter\Ast\NodeAttribute;
use Asmblah\PhpCodeShift\Shifter\Printer\NodePrinterInterface;
use Asmblah\PhpCodeShift\Shifter\Resolver\NodeResolverInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Context\ModificationContextInterface;
use LogicException;
use PhpParser\Node;
use PhpParser\NodeTraverser;

/**
 * Class ModificationVisitor.
 *
 * Handles processing of AST nodes.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ModificationVisitor extends AbstractNodeVisitor
{
    public function __construct(
        private readonly ModificationContextInterface $modificationContext,
        private readonly NodeResolverInterface $nodeResolver,
        private readonly NodePrinterInterface $nodePrinter
    ) {
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        $replacedNode = $this->nodeResolver->extractReplacedNode($node);

        if ($replacedNode === null) {
            // Early-out; node is not a replacement for an earlier one.
            return null;
        }

        $start = $replacedNode->getAttribute(NodeAttribute::START_FILE_POS);
        $length = $replacedNode->getAttribute(NodeAttribute::END_FILE_POS) -
            $replacedNode->getAttribute(NodeAttribute::START_FILE_POS) + 1;
        $startLine = $replacedNode->getAttribute(NodeAttribute::START_LINE);
        $endLine = $replacedNode->getAttribute(NodeAttribute::END_LINE);

        $printedReplacement = $this->nodePrinter->printNode($node, $startLine, $this->modificationContext);

        // Check that the replacement will preserve line numbers.
        if ($printedReplacement->getStartLine() !== $startLine) {
            throw new LogicException(
                sprintf(
                    'Replacement start line must be %d but it will be %d',
                    $startLine,
                    $printedReplacement->getStartLine()
                )
            );
        }

        if ($printedReplacement->getEndLine() !== $endLine) {
            throw new LogicException(
                sprintf(
                    'Replacement end line must be %d but it will be %d',
                    $endLine,
                    $printedReplacement->getEndLine()
                )
            );
        }

        $replacementCode = $printedReplacement->getCode();

        $delta = $this->modificationContext->getDelta();

        // Apply the delta of this replacement to all subsequent ones.
        $this->modificationContext->setDelta($delta + (strlen($replacementCode) - $length));

        $this->modificationContext->setContents(
            substr_replace($this->modificationContext->getContents(), $replacementCode, $start + $delta, $length)
        );

        // Child nodes will have been handled by the recursive node printing above.
        return NodeTraverser::DONT_TRAVERSE_CHILDREN;
    }
}
