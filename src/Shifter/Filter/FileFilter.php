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
     * @var string
     */
    private string $regex;

    public function __construct(
        private readonly string $pattern
    ) {
        $pattern = preg_quote($pattern, '#');
        $separator = preg_quote(DIRECTORY_SEPARATOR, '#');

        // Support both star and globstar.
        $pattern = str_replace(['\*\*', '\*'], ['[\s\S]*?', '[^' . $separator . ']*?'], $pattern);

        if (str_ends_with($pattern, DIRECTORY_SEPARATOR)) {
            // Ensure trailing slash can be omitted for directories.
            $pattern .= '?';
        }

        $patterns = [$pattern];

        foreach (StreamWrapper::PROTOCOLS as $protocol) {
            $patterns[] = $protocol . '://' . $pattern;
        }

        $this->regex = '#\A(?:' . implode('|', $patterns) . ')\Z#';
    }

    /**
     * @inheritDoc
     */
    public function fileMatches(string $path): bool
    {
        // Trim to ensure trailing slash can be omitted for directories.
        return preg_match($this->regex, rtrim($path, DIRECTORY_SEPARATOR)) === 1;
    }

    /**
     * Fetches the glob-style pattern defined for this filter.
     *
     * @return string
     */
    public function getPattern(): string
    {
        return $this->pattern;
    }

    /**
     * Fetches the regex pattern defined for this filter.
     *
     * @return string
     */
    public function getRegex(): string
    {
        return $this->regex;
    }
}
