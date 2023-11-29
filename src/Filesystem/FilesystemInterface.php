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

namespace Asmblah\PhpCodeShift\Filesystem;

use Asmblah\PhpCodeShift\Exception\NativeFileOperationFailedException;
use RegexIterator;

/**
 * Interface FilesystemInterface.
 *
 * Abstraction over the filesystem.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface FilesystemInterface
{
    /**
     * Determines whether a directory exists at the given path.
     */
    public function directoryExists(string $path): bool;

    /**
     * Determines whether a file exists at the given path.
     */
    public function fileExists(string $path): bool;

    /**
     * Fetches all file paths matching the given pattern.
     *
     * @return string[]
     */
    public function glob(string $pattern): array;

    /**
     * Returns an iterator over files in the given directory.
     */
    public function iterateDirectory(string $path, string $pattern): RegexIterator;

    /**
     * Creates the given directory, if it does not already exist.
     */
    public function mkdir(string $path): void;

    /**
     * Opens the given file path for reading only.
     *
     * @return resource
     */
    public function openForRead(string $path);

    /**
     * Reads the entire contents of a file.
     */
    public function readFile(string $path): string;

    /**
     * Removes the given file or directory recursively.
     */
    public function remove(string $path): void;

    /**
     * Writes the given data to the given file path.
     *
     * @throws NativeFileOperationFailedException On failure.
     */
    public function writeFile(string $path, string $contents): void;
}
