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
use Asmblah\PhpCodeShift\Shifter\Shift\Traverser\AbstractNodeVisitor;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;

/**
 * Class CallVisitor.
 *
 * Transforms call AST nodes to apply the function hook logic.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class CallVisitor extends AbstractNodeVisitor
{
    public function __construct(
        private readonly FunctionHookShiftSpec $shiftSpec
    ) {
    }

    /**
     * Replaces function calls with a static call to the special Invoker class for hooking.
     */
    public function enterNode(Node $node)
    {
        if (
            $node instanceof FuncCall &&
            $node->name instanceof Name &&
            $node->name->toCodeString() === $this->shiftSpec->getFunctionName()
        ) {
            return new StaticCall(
                new Name('\\' . Invoker::class),
                $this->shiftSpec->getFunctionName(),
                $node->args
            );
        }

        return null; // Leave the node unchanged.
    }
}
