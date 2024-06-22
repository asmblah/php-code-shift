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

    /**
     * @inheritDoc
     */
    public function addFilter(FileFilterInterface $filter): void
    {
        $this->filters[] = $filter;
    }

    /**
     * @inheritDoc
     */
    public function clear(): void
    {
        $this->filters = [];
    }

    /**
     * @inheritDoc
     */
    public function fileMatches(string $path): bool
    {
        foreach ($this->filters as $filter) {
            if ($filter->fileMatches($path)) {
                return true;
            }
        }

        return false;
    }
}
