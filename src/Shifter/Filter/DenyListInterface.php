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
 * Class DenyList.
 *
 * Specifies which files should never be transpiled.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface DenyListInterface extends FileFilterInterface
{
    /**
     * Adds a new filter for files that should never be transpiled.
     */
    public function addFilter(FileFilterInterface $filter): void;

    /**
     * Removes all filters from the deny list.
     */
    public function clear(): void;
}
