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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Shift\Tock;

use Asmblah\PhpCodeShift\Shifter\Shift\Spec\ShiftSpecInterface;
use PhpParser\Node\Stmt;

/**
 * Class TockStatementShiftSpec.
 *
 * Defines a shift that will add the given statement at the entry of userland functions
 * and top of loop bodies.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class TockStatementShiftSpec implements ShiftSpecInterface
{
    /**
     * @var callable
     */
    private $statementNodeFetcher;

    public function __construct(
        callable $statementNodeFetcher
    ) {
        $this->statementNodeFetcher = $statementNodeFetcher;
    }

    /**
     * Fetches the statement AST node to add.
     */
    public function createStatementNode(): Stmt
    {
        return ($this->statementNodeFetcher)();
    }

    /**
     * @inheritDoc
     */
    public function init(): void
    {
    }
}
