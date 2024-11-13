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

namespace Asmblah\PhpCodeShift\Shifter\Stream\Handler\Registration;

use Asmblah\PhpCodeShift\Shifter\Stream\Handler\StreamHandlerInterface;

/**
 * Class Registration.
 *
 * Represents a stream handler to be registered.
 *
 * @template T of StreamHandlerInterface
 * @template-extends AbstractRegistration<T>
 * @author Dan Phillimore <dan@ovms.co>
 */
class Registration extends AbstractRegistration
{
}
