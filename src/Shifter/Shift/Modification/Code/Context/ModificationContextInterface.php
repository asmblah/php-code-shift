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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Modification\Code\Context;

use Asmblah\PhpCodeShift\Shifter\Shift\Context\ContextInterface;

/**
 * Interface ModificationContextInterface.
 *
 * Represents the current context of the modification.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface ModificationContextInterface extends ContextInterface
{
    /**
     * Fetches the current contents.
     */
    public function getContents(): string;

    /**
     * Fetches the current delta.
     */
    public function getDelta(): int;

    /**
     * Sets the current contents.
     */
    public function setContents(string $contents): void;

    /**
     * Updates the current delta.
     */
    public function setDelta(int $delta): void;
}
