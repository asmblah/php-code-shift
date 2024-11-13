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
 * Interface RegistrationInterface.
 *
 * Represents a registered stream handler.
 *
 * @template-covariant T of StreamHandlerInterface
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface RegistrationInterface
{
    /**
     * Fetches the registered stream handler.
     *
     * @phpstan-return T
     */
    public function getStreamHandler(): StreamHandlerInterface;

    /**
     * Changes the reference to another stream handler from the handler to the provided new one.
     */
    public function redecorate(StreamHandlerInterface $newWrappedStreamHandler): void;

    /**
     * Performs the registration of the stream handler.
     */
    public function register(): void;

    /**
     * Replaces the registered stream handler with another.
     *
     * Usually, the provided replacement registration will be returned,
     * but a different one can be returned in its place if needed.
     *
     * @template S of StreamHandlerInterface
     * @param RegistrationInterface<S> $replacementRegistration
     * @return RegistrationInterface<S>
     */
    public function replace(RegistrationInterface $replacementRegistration): RegistrationInterface;

    /**
     * Unregisters the stream handler, restoring the previous one.
     */
    public function unregister(): void;
}
