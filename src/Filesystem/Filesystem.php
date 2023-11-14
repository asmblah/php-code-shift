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
 * Class Filesystem.
 *
 * Abstraction over the filesystem.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Filesystem implements FilesystemInterface
{
    /**
     * @inheritDoc
     */
    public function fileExists(string $path): bool
    {
        return is_file($path);
    }

    /**
     * @inheritDoc
     */
    public function openForRead(string $path)
    {
        return fopen($path, 'rb');
    }

    /**
     * @inheritDoc
     */
    public function writeFile(string $path, string $contents): void
    {
        if (file_put_contents($path, $contents) === false) {
            throw new NativeFileOperationFailedException(
                sprintf(
                    'Failed to write %d byte(s) to file path: "%s"',
                    strlen($contents),
                    $path
                )
            );
        }
    }
}
