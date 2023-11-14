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
     * Determines whether a file exists at the given path.
     */
    public function fileExists(string $path): bool;

    /**
     * Opens the given file path for reading only.
     *
     * @return resource
     */
    public function openForRead(string $path);

    /**
     * Writes the given data to the given file path.
     *
     * @throws NativeFileOperationFailedException On failure.
     */
    public function writeFile(string $path, string $contents): void;
}
