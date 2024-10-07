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
 * Class ExceptFilter.
 *
 * Specifies a filter to always fail to match for, otherwise matching against another filter.
 * Useful when you need an initial guard filter, to exclude something, such as a library
 * that should never be transpiled.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ExceptFilter implements FileFilterInterface
{
    private readonly string $regex;
    private readonly string $regexPart;

    public function __construct(
        private readonly FileFilterInterface $exceptFilter,
        private readonly FileFilterInterface $onlyFilter
    ) {
        $this->regexPart = '(?!' . $exceptFilter->getRegexPart() . ')(?:' . $onlyFilter->getRegexPart() . ')';
        $this->regex = '#\A(?:' . $this->regexPart . ')\Z#';
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
     * Fetches the filter to exclude.
     */
    public function getExceptFilter(): FileFilterInterface
    {
        return $this->exceptFilter;
    }

    /**
     * Fetches the filter to include only matches of, and only when the except filter is not matched.
     */
    public function getOnlyFilter(): FileFilterInterface
    {
        return $this->onlyFilter;
    }

    /**
     * @inheritDoc
     */
    public function getRegexPart(): string
    {
        return $this->regexPart;
    }
}
