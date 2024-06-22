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

namespace Asmblah\PhpCodeShift\Shifter\Printer;

/**
 * Class PrintedNode.
 *
 * Represents an AST node that has been printed to code string.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class PrintedNode implements PrintedNodeInterface
{
    public function __construct(
        private readonly string $code,
        private readonly int $startLine,
        private readonly int $endLine
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @inheritDoc
     */
    public function getEndLine(): int
    {
        return $this->endLine;
    }

    /**
     * @inheritDoc
     */
    public function getStartLine(): int
    {
        return $this->startLine;
    }
}
