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

namespace Asmblah\PhpCodeShift\Filesystem\Access;

/**
 * Interface AccessResolverInterface.
 *
 * Abstraction over resolving file or directory access.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface AccessResolverInterface
{
    /**
     * Determines whether the given path is executable.
     */
    public function isExecutable(string $path): bool;

    /**
     * Determines whether the given path is readable.
     */
    public function isReadable(string $path): bool;

    /**
     * Determines whether the given path is writable.
     */
    public function isWritable(string $path): bool;
}
