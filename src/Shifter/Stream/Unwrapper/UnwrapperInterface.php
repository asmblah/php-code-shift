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

namespace Asmblah\PhpCodeShift\Shifter\Stream\Unwrapper;

/**
 * Interface UnwrapperInterface.
 *
 * Permits temporary access to the native stream wrapper, for accessing the actual filesystem.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface UnwrapperInterface
{
    /**
     * Disables the stream wrapper while the given callback is executed,
     * allowing the native file:// protocol stream wrapper to be used for actual filesystem access.
     */
    public function unwrapped(callable $callback): mixed;
}
