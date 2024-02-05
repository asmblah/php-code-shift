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
use PhpParser\Error;
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

        $modificationTraverser = new AstModificationTraverser();

        // Set up AST modifications.
        foreach ($shiftSet->getShifts() as $shift) {
            $shift->configureTraversal($modificationTraverser, $shiftContext);
        }

        $context = new ModificationContext($shiftContext);

        $modificationTraverser->addLibraryVisitor(
            new ModificationVisitor(
                $context,
                $this->extentResolver,
                $this->nodePrinter
            )
        );

        /*
         * - Perform AST modifications
         * - Perform code modifications according to the AST modifications made.
         * - Process all nodes that have changed in the AST,
         *   generating each changed node's replacement code and replacing it in the code string.
         */
        $modificationTraverser->traverse($nodes);

        // Return the final modified contents.
        return $context->getContents();
    }
}
