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

namespace Asmblah\PhpCodeShift\Shifter\Resolver;

/**
 * Interface CodeModificationExtentsInterface.
 *
 * Represents the extents within which a code modification should take place.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface CodeModificationExtentsInterface
{
    /**
     * Fetches the end line.
     */
    public function getEndLine(): int;

    /**
     * Fetches the end code offset.
     */
    public function getEndOffset(): int;

    /**
     * Fetches the starting line.
     */
    public function getStartLine(): int;

    /**
     * Fetches the starting code offset.
     */
    public function getStartOffset(): int;
}
