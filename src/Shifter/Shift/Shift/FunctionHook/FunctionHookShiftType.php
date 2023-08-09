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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Shift\FunctionHook;

use Asmblah\PhpCodeShift\Shifter\Shift\Shift\ShiftTypeInterface;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\PrettyPrinterAbstract;

/**
 * Class FunctionHookShiftType.
 *
 * Defines a shift that will hook the given PHP function, allowing a replacement
 * implementation to be substituted that is able to defer to the original as needed.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class FunctionHookShiftType implements ShiftTypeInterface
{
    public function __construct(
        private readonly Parser $parser,
        private readonly PrettyPrinterAbstract $prettyPrinter
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getShifter(): callable
    {
        return $this->shift(...);
    }

    /**
     * @inheritDoc
     */
    public function getShiftSpecFqcn(): string
    {
        return FunctionHookShiftSpec::class;
    }

    /**
     * Applies the shift to the contents.
     */
    public function shift(FunctionHookShiftSpec $shiftSpec, string $contents): string
    {
        $nodeTraverser = new NodeTraverser();

        $callVisitor = new CallVisitor($shiftSpec);
        $nodeTraverser->addVisitor($callVisitor);

        $ast = $this->parser->parse($contents);

        $ast = $nodeTraverser->traverse($ast);

        if (!$callVisitor->madeModifications()) {
            // Don't regenerate the file if nothing was changed.
            return $contents;
        }

        return $this->prettyPrinter->prettyPrintFile($ast);
    }
}
