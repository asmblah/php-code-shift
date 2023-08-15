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

namespace Asmblah\PhpCodeShift\Util;

use Asmblah\PhpCodeShift\Exception\NoNativeCallerAvailableException;

/**
 * Class CallStack.
 *
 * Provides utilities for working with the call stack.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class CallStack implements CallStackInterface
{
    /**
     * @inheritDoc
     */
    public function getNativeFunctionName(): string
    {
        $frames = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        foreach ($frames as $index => $frame) {
            if (!array_key_exists('line', $frame)) {
                $functionName = $frames[$index + 1]['function'] ?? null;

                if ($functionName !== null) {
                    return $functionName;
                }
            }

            if (!array_key_exists('class', $frame)) {
                $functionName = $frame['function'] ?? null;

                if (in_array($functionName, ['include', 'include_once', 'require', 'require_once'], true)) {
                    return $functionName;
                }
            }
        }

        throw new NoNativeCallerAvailableException();
    }
}
