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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Shift\String;

use Asmblah\PhpCodeShift\Shifter\Shift\Spec\ShiftSpecInterface;

/**
 * Class StringShiftSpec.
 *
 * Defines a shift that will perform the given string replacement.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StringShiftSpec implements ShiftSpecInterface
{
    public function __construct(
        private string $needle,
        private string $replacement
    ) {
    }

    /**
     * Fetches the name of the string to find.
     */
    public function getNeedle(): string
    {
        return $this->needle;
    }

    /**
     * Fetches the replacement string.
     */
    public function getReplacement(): string
    {
        return $this->replacement;
    }

    /**
     * @inheritDoc
     */
    public function init(): void
    {
    }
}
