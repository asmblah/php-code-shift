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

use Exception;

/**
 * Class NoWrappedResourceAvailableException.
 *
 * Represents that there is no underlying resource available to cast as.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class NoWrappedResourceAvailableException extends Exception implements ExceptionInterface
{
}
