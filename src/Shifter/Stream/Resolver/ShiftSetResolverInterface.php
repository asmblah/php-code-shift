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

namespace Asmblah\PhpCodeShift\Shifter\Stream\Resolver;

use Asmblah\PhpCodeShift\Shifter\Shift\ShiftSetInterface;

/**
 * Interface ShiftSetResolverInterface.
 *
 * Resolves the ShiftSet of shifts applicable to a given PHP module file path.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface ShiftSetResolverInterface
{
    /**
     * Resolves the applicable ShiftSet for the given path, if any.
     */
    public function resolveShiftSet(string $path): ?ShiftSetInterface;
}
