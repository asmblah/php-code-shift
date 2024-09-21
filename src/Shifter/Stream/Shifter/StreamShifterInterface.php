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
     * Applies shifts to the given path, opening it as a stream if needed,
     * and returning a new resource with shifts performed if applicable.
     *
     * Returns null if the stream cannot be opened.
     *
     * @param string $path
     * @param callable(): (resource|null) $openStream
     * @return resource|null
     */
    public function shift(string $path, callable $openStream);
}
