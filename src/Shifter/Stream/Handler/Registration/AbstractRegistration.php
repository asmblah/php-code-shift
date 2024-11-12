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
use Asmblah\PhpCodeShift\Shifter\Stream\StreamWrapperManager;

/**
 * Class AbstractRegistration.
 *
 * Represents a stream handler to be registered.
 *
 * @template T of StreamHandlerInterface
 * @template-implements RegistrationInterface<T>
 * @author Dan Phillimore <dan@ovms.co>
 */
abstract class AbstractRegistration implements RegistrationInterface
{
    /**
     * @phpstan-param T $streamHandler
     */
    public function __construct(
        protected readonly StreamHandlerInterface $streamHandler,
        protected readonly StreamHandlerInterface $previousStreamHandler
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getStreamHandler(): StreamHandlerInterface
    {
        return $this->streamHandler;
    }

    /**
     * @inheritDoc
     */
    public function redecorate(StreamHandlerInterface $newWrappedStreamHandler): void
    {
        $this->streamHandler->redecorate($newWrappedStreamHandler);
    }

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        StreamWrapperManager::setStreamHandler($this->streamHandler);
    }

    /**
     * @inheritDoc
     */
    public function replace(RegistrationInterface $replacementRegistration): RegistrationInterface
    {
        return $replacementRegistration;
    }

    /**
     * @inheritDoc
     */
    public function unregister(): void
    {
        // Assuming the stream handler has not changed in the meantime,
        // restore the stream handler that was in use previously.
        if (StreamWrapperManager::getStreamHandler() === $this->streamHandler) {
            StreamWrapperManager::setStreamHandler($this->previousStreamHandler);
        }
    }
}
