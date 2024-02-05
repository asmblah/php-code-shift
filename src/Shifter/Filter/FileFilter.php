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

use Asmblah\PhpCodeShift\Shifter\Stream\Native\StreamWrapper;

/**
 * Class FileFilter.
 *
 * Specifies which files a shift should be applied to with a glob pattern.
 * Checks against all supported protocols plus the empty protocol.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class FileFilter implements FileFilterInterface
{
    /**
     * @var string[]
     */
    private array $patterns;

    public function __construct(string $pattern)
    {
        foreach (StreamWrapper::PROTOCOLS as $protocol) {
            $this->patterns[] = $protocol . '://' . $pattern;
        }

        $this->patterns[] = $pattern;
    }

    /**
     * @inheritDoc
     */
    public function fileMatches(string $path): bool
    {
        foreach ($this->patterns as $pattern) {
            if (fnmatch($pattern, $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fetches the patterns defined for this filter.
     *
     * @return string[]
     */
    public function getPatterns(): array
    {
        return $this->patterns;
    }
}
