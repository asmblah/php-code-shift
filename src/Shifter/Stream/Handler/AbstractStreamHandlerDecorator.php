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

namespace Asmblah\PhpCodeShift\Shifter\Stream\Handler;

/**
 * Class AbstractStreamHandlerDecorator.
 *
 * Base class to ease implementation of partial decorators.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
abstract class AbstractStreamHandlerDecorator implements StreamHandlerInterface
{
    public function __construct(
        protected StreamHandlerInterface $wrappedStreamHandler
    ) {
    }

    /**
     * @inheritDoc
     */
    public function closeDir($wrappedResource): bool
    {
        return $this->wrappedStreamHandler->closeDir($wrappedResource);
    }

    /**
     * @inheritDoc
     */
    public function openDir($context, string $path, int $options)
    {
        return $this->wrappedStreamHandler->openDir($context, $path, $options);
    }

    /**
     * @inheritDoc
     */
    public function readDir($wrappedResource): string|false
    {
        return $this->wrappedStreamHandler->readDir($wrappedResource);
    }

    /**
     * @inheritDoc
     */
    public function rewindDir($wrappedResource): bool
    {
        return $this->wrappedStreamHandler->rewindDir($wrappedResource);
    }

    /**
     * @inheritDoc
     */
    public function mkdir($context, string $path, int $mode, int $options): bool
    {
        return $this->wrappedStreamHandler->mkdir($context, $path, $mode, $options);
    }

    /**
     * @inheritDoc
     */
    public function rename($context, string $fromPath, string $toPath): bool
    {
        return $this->wrappedStreamHandler->rename($context, $fromPath, $toPath);
    }

    /**
     * @inheritDoc
     */
    public function rmdir($context, string $path, int $options): bool
    {
        return $this->wrappedStreamHandler->rmdir($context, $path, $options);
    }

    /**
     * @inheritDoc
     */
    public function streamCast($wrappedResource, int $castAs)
    {
        return $this->wrappedStreamHandler->streamCast($wrappedResource, $castAs);
    }

    /**
     * @inheritDoc
     */
    public function streamClose($wrappedResource): void
    {
        $this->wrappedStreamHandler->streamClose($wrappedResource);
    }

    /**
     * @inheritDoc
     */
    public function streamEof($wrappedResource): bool
    {
        return $this->wrappedStreamHandler->streamEof($wrappedResource);
    }

    /**
     * @inheritDoc
     */
    public function streamFlush($wrappedResource): bool
    {
        return $this->wrappedStreamHandler->streamFlush($wrappedResource);
    }

    /**
     * @inheritDoc
     */
    public function streamLock($wrappedResource, int $operation): bool
    {
        return $this->wrappedStreamHandler->streamLock($wrappedResource, $operation);
    }

    /**
     * @inheritDoc
     */
    public function streamMetadata(string $path, int $option, mixed $value): bool
    {
        return $this->wrappedStreamHandler->streamMetadata($path, $option, $value);
    }

    /**
     * @inheritDoc
     */
    public function streamOpen(
        $context,
        string $path,
        string $mode,
        int $options,
        ?string &$openedPath
    ) {
        return $this->wrappedStreamHandler->streamOpen($context, $path, $mode, $options, $openedPath);
    }

    /**
     * @inheritDoc
     */
    public function streamRead($wrappedResource, int $count): string|false
    {
        return $this->wrappedStreamHandler->streamRead($wrappedResource, $count);
    }

    /**
     * @inheritDoc
     */
    public function streamSeek($wrappedResource, int $offset, int $whence = SEEK_SET): bool
    {
        return $this->wrappedStreamHandler->streamSeek($wrappedResource, $offset, $whence);
    }

    /**
     * @inheritDoc
     */
    public function streamSetOption($wrappedResource, int $option, int $arg1, int $arg2): bool
    {
        return $this->wrappedStreamHandler->streamSetOption($wrappedResource, $option, $arg1, $arg2);
    }

    /**
     * @inheritDoc
     */
    public function streamStat($wrappedResource): array|false
    {
        return $this->wrappedStreamHandler->streamStat($wrappedResource);
    }

    /**
     * @inheritDoc
     */
    public function streamTell($wrappedResource): int|false
    {
        return $this->wrappedStreamHandler->streamTell($wrappedResource);
    }

    /**
     * @inheritDoc
     */
    public function streamTruncate($wrappedResource, int $newSize): bool
    {
        return $this->wrappedStreamHandler->streamTruncate($wrappedResource, $newSize);
    }

    /**
     * @inheritDoc
     */
    public function streamWrite($wrappedResource, string $data): int|false
    {
        return $this->wrappedStreamHandler->streamWrite($wrappedResource, $data);
    }

    /**
     * @inheritDoc
     */
    public function unlink($context, string $path): bool
    {
        return $this->wrappedStreamHandler->unlink($context, $path);
    }

    /**
     * @inheritDoc
     */
    public function urlStat(string $path, int $flags): array|false
    {
        return $this->wrappedStreamHandler->urlStat($path, $flags);
    }

    /**
     * @inheritDoc
     */
    public function unwrapped(callable $callback): mixed
    {
        return $this->wrappedStreamHandler->unwrapped($callback);
    }
}
