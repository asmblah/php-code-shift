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

namespace Asmblah\PhpCodeShift\Filesystem;

/**
 * Interface CanonicaliserInterface.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface CanonicaliserInterface
{
    /**
     * Canonicalises the given path, resolving all "./", "../", "//" symbols etc.
     *
     * Similar to the built-in realpath(...) function, except symlinks are not resolved,
     * and no filesystem hit is incurred.
     * Uses the current working directory for the process if none is specified.
     */
    public function canonicalise(string $path, ?string $cwd = null): string;
}
