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
 * Interface PrintedNodeInterface.
 *
 * Represents an AST node that has been printed to code string.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface PrintedNodeInterface
{
    /**
     * Fetches the printed code for the AST node.
     */
    public function getCode(): string;

    /**
     * Fetches the end line the printed code will have.
     */
    public function getEndLine(): int;

    /**
     * Fetches the start line the printed code will have.
     */
    public function getStartLine(): int;
}
