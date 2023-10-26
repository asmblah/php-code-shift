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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Modification\Code;

use Asmblah\PhpCodeShift\Shifter\Printer\NodePrinterInterface;
use Asmblah\PhpCodeShift\Shifter\Resolver\ExtentResolverInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Code\Context\ModificationContextInterface;
use LogicException;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * Class ModificationVisitor.
 *
 * Handles processing of AST nodes that were modified by shifts.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ModificationVisitor extends NodeVisitorAbstract
{
    public function __construct(
        private readonly ModificationContextInterface $modificationContext,
        private readonly ExtentResolverInterface $extentResolver,
        private readonly NodePrinterInterface $nodePrinter
    ) {
    }

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        $modificationExtents = $this->extentResolver->resolveModificationExtents($node, $this->modificationContext);

        if ($modificationExtents === null) {
            // Early-out; no modification has been made such as this node replacing an earlier one.
            return null;
        }

        $start = $modificationExtents->getStartOffset();
        $length = $modificationExtents->getEndOffset() -
            $modificationExtents->getStartOffset()/* + 1*/;
        $startLine = $modificationExtents->getStartLine();
        $endLine = $modificationExtents->getEndLine();

        $printedNode = $this->nodePrinter->printNode($node, $startLine, $this->modificationContext);

        // Check that the modification will preserve line numbers.
        if ($printedNode->getStartLine() !== $startLine) {
            throw new LogicException(
                sprintf(
                    'Modification result\'s start line must be %d but it will be %d',
                    $startLine,
                    $printedNode->getStartLine()
                )
            );
        }

        if ($printedNode->getEndLine() !== $endLine) {
            throw new LogicException(
                sprintf(
                    'Modification result\'s end line must be %d but it will be %d',
                    $endLine,
                    $printedNode->getEndLine()
                )
            );
        }

        $printedCode = $printedNode->getCode();

        $delta = $this->modificationContext->getDelta();

        // Apply the delta of this modification to all subsequent ones.
        $this->modificationContext->setDelta($delta + (strlen($printedCode) - $length));

        $this->modificationContext->setContents(
            substr_replace($this->modificationContext->getContents(), $printedCode, $start + $delta, $length)
        );

        // Child nodes will have been handled by the recursive node printing above.
        return NodeTraverser::DONT_TRAVERSE_CHILDREN;
    }
}
