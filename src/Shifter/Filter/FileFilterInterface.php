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
 * Interface FileFilterInterface.
 *
 * Specifies which files a shift should be applied to.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface FileFilterInterface
{
    public function fileMatches(string $path): bool;
}
