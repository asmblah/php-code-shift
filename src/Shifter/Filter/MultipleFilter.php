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
 * Class MultipleFilter.
 *
 * Specifies which files a shift should be applied to by using a list of possible sub-filters.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class MultipleFilter implements FileFilterInterface
{
    private readonly string $regex;
    private readonly string $regexPart;

    /**
     * @param FileFilterInterface[] $subFilters
     */
    public function __construct(private readonly array $subFilters)
    {
        $this->regexPart = implode(
            '|',
            array_map(
                static fn (FileFilterInterface $filter) => $filter->getRegexPart(),
                $subFilters
            )
        );
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
     * @inheritDoc
     */
    public function getRegexPart(): string
    {
        return $this->regexPart;
    }

    /**
     * Fetches the filters that make up this alternation.
     *
     * @return FileFilterInterface[]
     */
    public function getSubFilters(): array
    {
        return $this->subFilters;
    }
}
