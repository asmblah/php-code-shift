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

use Asmblah\PhpCodeShift\Exception\ParseFailedException;
use Asmblah\PhpCodeShift\Shifter\Printer\NodePrinterInterface;
use Asmblah\PhpCodeShift\Shifter\Resolver\ExtentResolverInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Context\ShiftContext;
use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Code\Context\ModificationContext;
use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Code\ModificationVisitor;
use Asmblah\PhpCodeShift\Shifter\Shift\ShiftSetInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Traverser\Ast\AstModificationTraverser;
use Asmblah\PhpCodeShift\Shifter\Shift\Traverser\Code\CodeModificationTraverser;
use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
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
        private readonly ExtentResolverInterface $extentResolver,
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

        try {
            $nodes = $this->parser->parse($contents);
        } catch (Error $exception) {
            throw new ParseFailedException(
                $shiftSet->getPath(),
                $exception
            );
        }

        // Resolve names (e.g. class identifiers -> FQCNs).
        $nameResolvingTraverser = new NodeTraverser();
        $nameResolvingTraverser->addVisitor(new NameResolver());
        $nameResolvingTraverser->traverse($nodes);

        // Set up AST modifications.
        $astModificationTraverser = new AstModificationTraverser();

        foreach ($shiftSet->getShifts() as $shift) {
            $shift->configureTraversal($astModificationTraverser, $shiftContext);
        }

        // Perform AST modifications.
        do {
            $previousNodes = $nodes;
            $nodes = $astModificationTraverser->traverse($nodes);
        } while ($nodes !== $previousNodes);

        $context = new ModificationContext($shiftContext);

        // Now perform code modifications according to the AST modifications made above.

        // Process all nodes that have changed in the AST,
        // generating each changed node's replacement code and replacing it in the code string.
        $codeModificationTraverser = new CodeModificationTraverser();
        $codeModificationTraverser->addVisitor(
            new ModificationVisitor(
                $context,
                $this->extentResolver,
                $this->nodePrinter
            )
        );
        $codeModificationTraverser->traverse($nodes);

        // Return the final modified contents.
        return $context->getContents();
    }
}
