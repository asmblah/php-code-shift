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
use Asmblah\PhpCodeShift\Shifter\Stream\Unwrapper\UnwrapperInterface;

/**
 * Interface StreamHandlerInterface.
 *
 * Defines the interface to the low-level filesystem API.
 * May be decorated to hook into filesystem operations.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface StreamHandlerInterface extends UnwrapperInterface
{
    public const STREAM_OPEN_FOR_INCLUDE = 128;

    /**
     * Closes the given directory that was opened for walking.
     */
    public function closeDir(StreamWrapperInterface $streamWrapper): bool;

    /**
     * Opens the given directory for walking.
     *
     * @return resource|null
     */
    public function openDir(StreamWrapperInterface $streamWrapper, string $path, int $options);

    /**
     * Reads the next file from the directory.
     */
    public function readDir(StreamWrapperInterface $streamWrapper): string|false;

    /**
     * Rewinds the directory walk to the beginning.
     */
    public function rewindDir(StreamWrapperInterface $streamWrapper): bool;

    /**
     * Creates a new directory as specified.
     */
    public function mkdir(StreamWrapperInterface $streamWrapper, string $path, int $mode, int $options): bool;

    /**
     * Renames the given path as specified.
     */
    public function rename(StreamWrapperInterface $streamWrapper, string $fromPath, string $toPath): bool;

    /**
     * Deletes the given directory.
     */
    public function rmdir(StreamWrapperInterface $streamWrapper, string $path, int $options): bool;

    /**
     * Casts this stream to a resource.
     *
     * @return resource
     */
    public function streamCast(StreamWrapperInterface $streamWrapper, int $castAs);

    /**
     * Closes the stream.
     */
    public function streamClose(StreamWrapperInterface $streamWrapper): void;

    /**
     * Determines whether we are at the end of this stream.
     */
    public function streamEof(StreamWrapperInterface $streamWrapper): bool;

    /**
     * Flushes data written to this stream.
     */
    public function streamFlush(StreamWrapperInterface $streamWrapper): bool;

    /**
     * Locks this stream as specified.
     */
    public function streamLock(StreamWrapperInterface $streamWrapper, int $operation): bool;

    /**
     * Sets the given metadata for this stream.
     */
    public function streamMetadata(string $path, int $option, mixed $value): bool;

    /**
     * Opens the given path for this stream.
     *
     * Returns both the opened stream resource and whether this is an include vs. normal file access on success.
     * Returns null on failure.
     *
     * @return array{resource: resource|null, isInclude: bool}|null
     */
    public function streamOpen(
        StreamWrapperInterface $streamWrapper,
        string $path,
        string $mode,
        int $options,
        ?string &$openedPath
    ): ?array;

    /**
     * Reads from the given stream.
     */
    public function streamRead(StreamWrapperInterface $streamWrapper, int $count): string|false;

    /**
     * Seeks the stream to a new position.
     */
    public function streamSeek(StreamWrapperInterface $streamWrapper, int $offset, int $whence = SEEK_SET): bool;

    /**
     * Sets the given stream option.
     */
    public function streamSetOption(StreamWrapperInterface $streamWrapper, int $option, int $arg1, int|null $arg2): bool;

    /**
     * Performs a stat of the given open stream.
     *
     * @return array<mixed>|false
     */
    public function streamStat(StreamWrapperInterface $streamWrapper): array|false;

    /**
     * Fetches the current position/offset within the stream.
     */
    public function streamTell(StreamWrapperInterface $streamWrapper): int|false;

    /**
     * Truncates the given stream.
     */
    public function streamTruncate(StreamWrapperInterface $streamWrapper, int $newSize): bool;

    /**
     * Writes the given data to the stream.
     */
    public function streamWrite(StreamWrapperInterface $streamWrapper, string $data): int|false;

    /**
     * Deletes the given file.
     */
    public function unlink(StreamWrapperInterface $streamWrapper, string $path): bool;

    /**
     * Performs a filesystem stat of the given path.
     *
     * @return array<mixed>|false
     */
    public function urlStat(string $path, int $flags): array|false;
}
