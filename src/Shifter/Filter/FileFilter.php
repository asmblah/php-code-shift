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

namespace Asmblah\PhpCodeShift\Shifter\Filter;

/**
 * Class FileFilter.
 *
 * Specifies which files a shift should be applied to with a glob pattern.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class FileFilter implements FileFilterInterface
{
    public function __construct(private readonly string $pattern)
    {
    }

    /**
     * @inheritDoc
     */
    public function fileMatches(string $path): bool
    {
        return fnmatch($this->pattern, $path);
    }
}
