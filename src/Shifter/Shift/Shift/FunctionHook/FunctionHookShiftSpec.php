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

use Asmblah\PhpCodeShift\Shifter\Hook\FunctionHooks;
use Asmblah\PhpCodeShift\Shifter\Shift\Spec\ShiftSpecInterface;

/**
 * Class FunctionHookShiftSpec.
 *
 * Defines a shift that will hook the given PHP function, allowing a replacement
 * implementation to be substituted that is able to defer to the original as needed.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class FunctionHookShiftSpec implements ShiftSpecInterface
{
    /**
     * @var callable
     */
    private $replacementProvider;

    public function __construct(
        private string $functionName,
        callable $replacementProvider
    ) {
        $this->replacementProvider = $replacementProvider;
    }

    /**
     * Fetches the name of the function to hook.
     */
    public function getFunctionName(): string
    {
        return $this->functionName;
    }

    /**
     * Fetches the replacement provider.
     */
    public function getReplacementProvider(): callable
    {
        return $this->replacementProvider;
    }

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        $replacement = ($this->replacementProvider)($this->functionName);

        FunctionHooks::installHook($this->functionName, $replacement);
    }
}
