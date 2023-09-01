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

namespace Asmblah\PhpCodeShift\Shifter\Modifier;

/**
 * Interface ContextInterface.
 *
 * Represents the current context of the modification.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface ContextInterface
{
    /**
     * Fetches the current delta.
     */
    public function getDelta(): int;

    /**
     * Updates the current delta.
     */
    public function setDelta(int $delta): void;
}
