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

namespace Asmblah\PhpCodeShift\Shifter\Parser;

use PhpParser\Parser;

/**
 * Interface ParserFactoryInterface.
 *
 * Creates an appropriate PHP parser instance.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface ParserFactoryInterface
{
    /**
     * Creates an appropriate PHP parser instance.
     */
    public function createParser(): Parser;
}
