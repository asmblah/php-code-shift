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

namespace Asmblah\PhpCodeShift\Cache\Warmer;

/**
 * Interface WarmerInterface.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface WarmerInterface
{
    /**
     * Warms the given file, pre-shifting it into the cache.
     */
    public function warmFile(string $filePath): void;
}
