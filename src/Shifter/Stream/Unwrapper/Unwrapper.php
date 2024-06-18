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

use Asmblah\PhpCodeShift\Shifter\Stream\Native\StreamWrapper;

/**
 * Class Unwrapper.
 *
 * Permits temporary access to the native stream wrapper, for accessing the actual filesystem.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Unwrapper implements UnwrapperInterface
{
    /**
     * @inheritDoc
     */
    public function unwrapped(callable $callback): mixed
    {
        StreamWrapper::unregister();

        try {
            return $callback();
        } finally {
            // Note that if we do not unregister again first following the above restore,
            // a segfault will be raised.
            StreamWrapper::register();
        }
    }
}
