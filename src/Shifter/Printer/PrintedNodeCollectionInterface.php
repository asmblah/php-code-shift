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
 * Interface PrintedNodeCollectionInterface.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface PrintedNodeCollectionInterface
{
    /**
     * Fetches the end line the printed code will have (usually the end line of the final node in the collection).
     */
    public function getEndLine(): int;

    /**
     * Fetches the printed nodes.
     *
     * @return PrintedNodeInterface[]
     */
    public function getPrintedNodes(): array;

    /**
     * Fetches the start line the printed code will have.
     */
    public function getStartLine(): int;

    /**
     * Concatenates the code of all nodes in the collection with the given delimiter.
     */
    public function join(string $delimiter): string;
}
