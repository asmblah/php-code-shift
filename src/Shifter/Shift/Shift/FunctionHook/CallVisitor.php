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

use Asmblah\PhpCodeShift\Shifter\Hook\Invoker;
use Asmblah\PhpCodeShift\Shifter\Shift\Spec\FunctionHookShiftSpec;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;

/**
 * Class CallVisitor.
 *
 * Transforms call AST nodes to apply the function hook logic.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class CallVisitor extends NodeVisitorAbstract
{
    private bool $modified = false;

    public function __construct(
        private readonly FunctionHookShiftSpec $shiftSpec
    ) {
    }

    public function enterNode(Node $node) {
        if (
            $node instanceof FuncCall &&
            $node->name->toCodeString() === $this->shiftSpec->getFunctionName()
        ) {
            $this->modified = true;

            return new StaticCall(
                new Name(Invoker::class),
                $this->shiftSpec->getFunctionName(),
                $node->args
            );
        }

        return null;
    }

    /**
     * Determines whether any calls were modified.
     */
    public function madeModifications(): bool
    {
        return $this->modified;
    }
}
