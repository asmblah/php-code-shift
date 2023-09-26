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

namespace Asmblah\PhpCodeShift\Shifter\Stream\Shifter;

use Asmblah\PhpCodeShift\Shifter\Shift\Shifter\ShiftSetShifterInterface;

/**
 * Interface StreamShifterInterface.
 *
 * Applies applicable shifts to an open stream of a PHP module file.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface StreamShifterInterface
{
    /**
     * Fetches the ShiftSetShifter service.
     */
    public function getShiftSetShifter(): ShiftSetShifterInterface;

    /**
     * Applies shifts to the given path and its open resource,
     * returning a new resource with shifts performed if applicable.
     *
     * @param resource $resource
     * @return resource
     */
    public function shift(string $path, $resource);
}
