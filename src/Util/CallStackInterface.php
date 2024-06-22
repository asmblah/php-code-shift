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

namespace Asmblah\PhpCodeShift\Util;

use Asmblah\PhpCodeShift\Exception\NoNativeCallerAvailableException;

/**
 * Interface CallStackInterface.
 *
 * Provides utilities for working with the call stack.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface CallStackInterface
{
    /**
     * Fetches the name of the native function that called the current frame.
     *
     * @throws NoNativeCallerAvailableException
     */
    public function getNativeFunctionName(): string;
}
