<?php

/*
 * PHP Code Shift - Monkey-patch PHP code on the fly.
 * Copyright (c) Dan Phillimore (asmblah)
 * https://github.com/asmblah/php-code-shift/
 *
 * Released under the MIT license.
 * https://github.com/asmblah/php-code-shift/raw/main/MIT-LICENSE.txt
 */

declare(strict_types=1);

namespace Asmblah\PhpCodeShift\Shifter\Shift\Shift\ClassHook;

use Asmblah\PhpCodeShift\Shifter\Hook\ClassHooks;
use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Ast\ModificationInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Ast\NodeReplacedModification;
use Asmblah\PhpCodeShift\Shifter\Shift\Traverser\Visitor\AbstractNodeVisitor;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;

/**
 * Class ClassReferenceVisitor.
 *
 * Transforms `new` instantiation, static method and static property lookup AST nodes to apply the class hook logic.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ClassReferenceVisitor extends AbstractNodeVisitor
{
    public function __construct(
        private readonly ClassHookShiftSpec $shiftSpec
    ) {
    }

    /**
     * Replaces class references with a static call to the special ClassHooks class for hooking.
     */
    public function enterNode(Node $node): ?ModificationInterface
    {
        if (
            $node instanceof New_ &&
            // Only support statically specified class names, for now.
            $node->class instanceof Name
        ) {
            $fullyQualifiedName = $node->class->toCodeString();

            if (ltrim($fullyQualifiedName, '\\') === $this->shiftSpec->getClassName()) {
                return new NodeReplacedModification(
                    $node,
                    new New_(
                        $this->createHookCall(),
                        $node->args
                    )
                );
            }
        }

        if (
            $node instanceof StaticCall &&
            // Only support statically specified class names, for now.
            $node->class instanceof Name
        ) {
            $fullyQualifiedName = $node->class->toCodeString();

            if (ltrim($fullyQualifiedName, '\\') === $this->shiftSpec->getClassName()) {
                return new NodeReplacedModification(
                    $node,
                    new StaticCall(
                        $this->createHookCall(),
                        $node->name,
                        $node->args
                    )
                );
            }
        }

        if (
            $node instanceof ClassConstFetch &&
            // Only support statically specified class names, for now.
            $node->class instanceof Name
        ) {
            $fullyQualifiedName = $node->class->toCodeString();

            if (ltrim($fullyQualifiedName, '\\') === $this->shiftSpec->getClassName()) {
                return new NodeReplacedModification(
                    $node,
                    new ClassConstFetch(
                        $this->createHookCall(),
                        $node->name
                    )
                );
            }
        }

        return null; // Leave the node unchanged.
    }

    private function createHookCall(): StaticCall
    {
        return new StaticCall(
            new Name('\\' . ClassHooks::class),
            'getReplacement',
            [
                new Arg(new String_($this->shiftSpec->getClassName()))
            ]
        );
    }
}
