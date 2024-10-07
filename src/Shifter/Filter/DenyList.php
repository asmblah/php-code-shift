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
 * Class DenyList.
 *
 * Specifies which files should never be transpiled.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class DenyList implements DenyListInterface
{
    /**
     * @var FileFilterInterface[]
     */
    private array $filters = [];
    private string $regex = '#(?!)#';
    private string $regexPart = '(?!)';

    /**
     * @inheritDoc
     */
    public function addFilter(FileFilterInterface $filter): void
    {
        $this->filters[] = $filter;

        /*
         * Re-build the regex every time the filter list is changed,
         * so that we don't pay the cost of branching on every match call
         * (which will usually be far higher than the number of changes).
         */
        $this->regexPart = implode(
            '|',
            array_map(
                static fn (FileFilterInterface $filter) => $filter->getRegexPart(),
                $this->filters
            )
        );
        $this->regex = '#\A(?:' . $this->regexPart . ')\Z#';
    }

    /**
     * @inheritDoc
     */
    public function clear(): void
    {
        $this->filters = [];
        $this->regex = '#(?!)#';
        $this->regexPart = '(?!)';
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
     * Fetches the filters added to the deny list.
     *
     * @return FileFilterInterface[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @inheritDoc
     */
    public function getRegexPart(): string
    {
        return $this->regexPart;
    }
}
