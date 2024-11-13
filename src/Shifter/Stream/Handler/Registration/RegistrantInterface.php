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
 * Interface RegistrantInterface.
 *
 * Creates a new stream handler to replace (usually chained with) the previous one.
 *
 * @template T of StreamHandlerInterface
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface RegistrantInterface
{
    /**
     * Registers a new stream handler.
     *
     * @return RegistrationInterface<T>
     */
    public function registerStreamHandler(
        StreamHandlerInterface $currentStreamHandler,
        ?StreamHandlerInterface $previousStreamHandler
    ): RegistrationInterface;
}
