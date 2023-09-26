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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Shifter;

use Asmblah\PhpCodeShift\Shifter\Printer\NodePrinterInterface;
use Asmblah\PhpCodeShift\Shifter\Resolver\NodeResolverInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Context\ModificationContext;
use Asmblah\PhpCodeShift\Shifter\Shift\Context\ShiftContext;
use Asmblah\PhpCodeShift\Shifter\Shift\ShiftSetInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Traverser\AstTraverser;
use Asmblah\PhpCodeShift\Shifter\Shift\Traverser\ModificationVisitor;
use Asmblah\PhpCodeShift\Shifter\Shift\Traverser\ShiftAstTraverser;
use PhpParser\Parser;

/**
 * Class ShiftSetShifter.
 *
 * Applies a set of shifts represented by a ShiftSet to a code string.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ShiftSetShifter implements ShiftSetShifterInterface
{
    public function __construct(
        private readonly Parser $parser,
        private readonly NodeResolverInterface $nodeResolver,
        private readonly NodePrinterInterface $nodePrinter
    ) {
    }

    /**
     * @inheritDoc
     */
    public function shift(
        string $contents,
        ShiftSetInterface $shiftSet
    ): string {
        $shiftContext = new ShiftContext($shiftSet, $contents);
        $nodes = $this->parser->parse($contents);

        $shiftAstTraverser = new ShiftAstTraverser();

        foreach ($shiftSet->getShifts() as $shift) {
            $shift->configureTraversal($shiftAstTraverser, $shiftContext);
        }

        do {
            $previousNodes = $nodes;
            $nodes = $shiftAstTraverser->traverse($nodes);
        } while ($nodes !== $previousNodes);

        $context = new ModificationContext($shiftContext);

        // Now process all nodes that have changed in the AST,
        // generating each changed node's replacement code and replacing it in the code string.
        $modificationAstTraverser = new AstTraverser();
        $modificationAstTraverser->addVisitor(
            new ModificationVisitor(
                $context,
                $this->nodeResolver,
                $this->nodePrinter
            )
        );
        $modificationAstTraverser->traverse($nodes);

        // Return the final modified contents.
        return $context->getContents();
    }
}
