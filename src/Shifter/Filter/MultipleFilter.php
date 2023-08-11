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
 * Class MultipleFilter.
 *
 * Specifies which files a shift should be applied to by using a list of possible sub-filters.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class MultipleFilter implements FileFilterInterface
{
    /**
     * @param FileFilterInterface[] $subFilters
     */
    public function __construct(private readonly array $subFilters)
    {
    }

    /**
     * @inheritDoc
     */
    public function fileMatches(string $path): bool
    {
        foreach ($this->subFilters as $subFilter) {
            if ($subFilter->fileMatches($path)) {
                return true;
            }
        }

        return false;
    }
}
