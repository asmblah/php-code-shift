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

namespace Asmblah\PhpCodeShift\Shifter\Filter;

/**
 * Interface FileFilterInterface.
 *
 * Specifies which files a shift should be applied to.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface FileFilterInterface
{
    /**
     * Determines whether the given file path matches this filter.
     */
    public function fileMatches(string $path): bool;

    /**
     * Fetches a regex for matching this filter without delimiters etc.,
     * suitable to be built into a larger single regex.
     */
    public function getRegexPart(): string;
}
