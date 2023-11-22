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
 * Class DirectoryNotFoundException.
 *
 * Represents that a given directory was not found for caching.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class DirectoryNotFoundException extends Exception implements ExceptionInterface
{
}
