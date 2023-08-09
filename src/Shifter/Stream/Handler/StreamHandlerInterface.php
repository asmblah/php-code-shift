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
 * Interface StreamHandlerInterface.
 *
 * Defines the interface to the low-level filesystem API.
 * May be decorated to hook into filesystem operations.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface StreamHandlerInterface
{
    /**
     * Closes the given directory that was opened for walking.
     *
     * @param resource $wrappedResource
     */
    public function closeDir($wrappedResource): bool;

    /**
     * Opens the given directory for walking.
     *
     * @param resource|null $context
     * @return resource|null
     */
    public function openDir($context, string $path, int $options);

    /**
     * Reads the next file from the directory.
     *
     * @param resource $wrappedResource
     */
    public function readDir($wrappedResource): string|false;

    /**
     * Rewinds the directory walk to the beginning.
     *
     * @param resource $wrappedResource
     */
    public function rewindDir($wrappedResource): bool;

    /**
     * Creates a new directory as specified.
     *
     * @param resource|null $context
     */
    public function mkdir($context, string $path, int $mode, int $options): bool;

    /**
     * Renames the given path as specified.
     *
     * @param resource|null $context
     */
    public function rename($context, string $fromPath, string $toPath): bool;

    /**
     * Deletes the given directory.
     *
     * @param resource|null $context
     */
    public function rmdir($context, string $path, int $options): bool;

    /**
     * Casts this stream to a resource.
     *
     * @return resource
     */
    public function streamCast($wrappedResource, int $castAs);

    /**
     * Closes the stream.
     *
     * @param resource $wrappedResource
     */
    public function streamClose($wrappedResource): void;

    /**
     * Determines whether we are at the end of this stream.
     *
     * @param resource $wrappedResource
     */
    public function streamEof($wrappedResource): bool;

    /**
     * Flushes data written to this stream.
     *
     * @param resource $wrappedResource
     */
    public function streamFlush($wrappedResource): bool;

    /**
     * Locks this stream as specified.
     *
     * @param resource $wrappedResource
     */
    public function streamLock($wrappedResource, int $operation): bool;

    /**
     * Sets the given metadata for this stream.
     */
    public function streamMetadata(string $path, int $option, mixed $value): bool;

    /**
     * Opens the given path for this stream.
     *
     * @param resource|null $context
     * @return resource|null
     */
    public function streamOpen(
        $context,
        string $path,
        string $mode,
        int $options,
        ?string &$openedPath
    );

    /**
     * Reads from the given stream.
     *
     * @param resource $wrappedResource
     */
    public function streamRead($wrappedResource, int $count): string|false;

    /**
     * Seeks the stream to a new position.
     *
     * @param resource $wrappedResource
     */
    public function streamSeek($wrappedResource, int $offset, int $whence = SEEK_SET): bool;

    /**
     * Sets the given stream option.
     *
     * @param resource $wrappedResource
     */
    public function streamSetOption($wrappedResource, int $option, int $arg1, int $arg2): bool;

    /**
     * Performs a stat of the given open stream.
     *
     * @param resource $wrappedResource
     */
    public function streamStat($wrappedResource): array|false;

    /**
     * Fetches the current position/offset within the stream.
     *
     * @param resource $wrappedResource
     */
    public function streamTell($wrappedResource): int|false;

    /**
     * Truncates the given stream.
     *
     * @param resource $wrappedResource
     */
    public function streamTruncate($wrappedResource, int $newSize): bool;

    /**
     * Writes the given data to the stream.
     *
     * @param resource $wrappedResource
     */
    public function streamWrite($wrappedResource, string $data): int|false;

    /**
     * Deletes the given file.
     *
     * @param resource|null $context
     */
    public function unlink($context, string $path): bool;

    /**
     * Performs a filesystem stat of the given path.
     */
    public function urlStat(string $path, int $flags): array|false;

    /**
     * Disables the stream wrapper while the given callback is executed,
     * allowing the native file:// protocol stream wrapper to be used for actual filesystem access.
     */
    public function unwrapped(callable $callback): mixed;
}
