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
    public function __construct(
        private readonly FileFilterInterface $exceptFilter,
        private readonly FileFilterInterface $onlyFilter
    ) {
    }

    /**
     * @inheritDoc
     */
    public function fileMatches(string $path): bool
    {
        if ($this->exceptFilter->fileMatches($path)) {
            return false;
        }

        return $this->onlyFilter->fileMatches($path);
    }
}
