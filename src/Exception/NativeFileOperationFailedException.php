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

namespace Asmblah\PhpCodeShift\Exception;

use Exception;

/**
 * Class NativeFileOperationFailedException.
 *
 * Represents that the native ftell(...)/fwrite(...) etc. call returned false.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class NativeFileOperationFailedException extends Exception implements ExceptionInterface
{
}
