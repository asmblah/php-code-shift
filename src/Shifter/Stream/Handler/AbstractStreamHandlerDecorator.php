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

namespace Asmblah\PhpCodeShift\Shifter\Stream\Handler;

use Asmblah\PhpCodeShift\Shifter\Stream\Native\StreamWrapperInterface;

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
    public function closeDir(StreamWrapperInterface $streamWrapper): bool
    {
        return $this->wrappedStreamHandler->closeDir($streamWrapper);
    }

    /**
     * @inheritDoc
     */
    public function openDir(StreamWrapperInterface $streamWrapper, string $path, int $options)
    {
        return $this->wrappedStreamHandler->openDir($streamWrapper, $path, $options);
    }

    /**
     * @inheritDoc
     */
    public function readDir(StreamWrapperInterface $streamWrapper): string|false
    {
        return $this->wrappedStreamHandler->readDir($streamWrapper);
    }

    /**
     * @inheritDoc
     */
    public function rewindDir(StreamWrapperInterface $streamWrapper): bool
    {
        return $this->wrappedStreamHandler->rewindDir($streamWrapper);
    }

    /**
     * @inheritDoc
     */
    public function mkdir(StreamWrapperInterface $streamWrapper, string $path, int $mode, int $options): bool
    {
        return $this->wrappedStreamHandler->mkdir($streamWrapper, $path, $mode, $options);
    }

    /**
     * @inheritDoc
     */
    public function rename(StreamWrapperInterface $streamWrapper, string $fromPath, string $toPath): bool
    {
        return $this->wrappedStreamHandler->rename($streamWrapper, $fromPath, $toPath);
    }

    /**
     * @inheritDoc
     */
    public function rmdir(StreamWrapperInterface $streamWrapper, string $path, int $options): bool
    {
        return $this->wrappedStreamHandler->rmdir($streamWrapper, $path, $options);
    }

    /**
     * @inheritDoc
     */
    public function streamCast(StreamWrapperInterface $streamWrapper, int $castAs)
    {
        return $this->wrappedStreamHandler->streamCast($streamWrapper, $castAs);
    }

    /**
     * @inheritDoc
     */
    public function streamClose(StreamWrapperInterface $streamWrapper): void
    {
        $this->wrappedStreamHandler->streamClose($streamWrapper);
    }

    /**
     * @inheritDoc
     */
    public function streamEof(StreamWrapperInterface $streamWrapper): bool
    {
        return $this->wrappedStreamHandler->streamEof($streamWrapper);
    }

    /**
     * @inheritDoc
     */
    public function streamFlush(StreamWrapperInterface $streamWrapper): bool
    {
        return $this->wrappedStreamHandler->streamFlush($streamWrapper);
    }

    /**
     * @inheritDoc
     */
    public function streamLock(StreamWrapperInterface $streamWrapper, int $operation): bool
    {
        return $this->wrappedStreamHandler->streamLock($streamWrapper, $operation);
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
        StreamWrapperInterface $streamWrapper,
        string $path,
        string $mode,
        int $options,
        ?string &$openedPath
    ): ?array {
        return $this->wrappedStreamHandler->streamOpen($streamWrapper, $path, $mode, $options, $openedPath);
    }

    /**
     * @inheritDoc
     */
    public function streamRead(StreamWrapperInterface $streamWrapper, int $count): string|false
    {
        return $this->wrappedStreamHandler->streamRead($streamWrapper, $count);
    }

    /**
     * @inheritDoc
     */
    public function streamSeek(StreamWrapperInterface $streamWrapper, int $offset, int $whence = SEEK_SET): bool
    {
        return $this->wrappedStreamHandler->streamSeek($streamWrapper, $offset, $whence);
    }

    /**
     * @inheritDoc
     */
    public function streamSetOption(StreamWrapperInterface $streamWrapper, int $option, int $arg1, int|null $arg2): bool
    {
        return $this->wrappedStreamHandler->streamSetOption($streamWrapper, $option, $arg1, $arg2);
    }

    /**
     * @inheritDoc
     */
    public function streamStat(StreamWrapperInterface $streamWrapper): array|false
    {
        return $this->wrappedStreamHandler->streamStat($streamWrapper);
    }

    /**
     * @inheritDoc
     */
    public function streamTell(StreamWrapperInterface $streamWrapper): int|false
    {
        return $this->wrappedStreamHandler->streamTell($streamWrapper);
    }

    /**
     * @inheritDoc
     */
    public function streamTruncate(StreamWrapperInterface $streamWrapper, int $newSize): bool
    {
        return $this->wrappedStreamHandler->streamTruncate($streamWrapper, $newSize);
    }

    /**
     * @inheritDoc
     */
    public function streamWrite(StreamWrapperInterface $streamWrapper, string $data): int|false
    {
        return $this->wrappedStreamHandler->streamWrite($streamWrapper, $data);
    }

    /**
     * @inheritDoc
     */
    public function unlink(StreamWrapperInterface $streamWrapper, string $path): bool
    {
        return $this->wrappedStreamHandler->unlink($streamWrapper, $path);
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
