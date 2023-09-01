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
use Asmblah\PhpCodeShift\Shifter\Modifier\AstNodeModification;
use Asmblah\PhpCodeShift\Shifter\Modifier\ModificationInterface;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
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
    /**
     * @var ModificationInterface[]
     */
    private array $modifications = [];

    public function __construct(
        private readonly FunctionHookShiftSpec $shiftSpec
    ) {
    }

    public function enterNode(Node $node) {
        if (
            $node instanceof FuncCall &&
            $node->name instanceof Name &&
            $node->name->toCodeString() === $this->shiftSpec->getFunctionName()
        ) {
            $this->modifications[] = new AstNodeModification(
                $node->name,
                '\\' . Invoker::class . '::' . $this->shiftSpec->getFunctionName()
            );
        }

        return null;
    }

    /**
     * Fetches all code modifications.
     *
     * @return ModificationInterface[]
     */
    public function getModifications(): array
    {
        return $this->modifications;
    }
}
