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

namespace Asmblah\PhpCodeShift\Exception;

use RuntimeException;

/**
 * Class NoNativeCallerAvailableException.
 *
 * Represents that no native caller function could be determined.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class NoNativeCallerAvailableException extends RuntimeException implements ExceptionInterface
{
}
