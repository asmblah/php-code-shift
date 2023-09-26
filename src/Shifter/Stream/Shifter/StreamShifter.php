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
use Asmblah\PhpCodeShift\Shifter\Stream\Resolver\ShiftSetResolverInterface;

/**
 * Class StreamShifter.
 *
 * Applies applicable shifts to an open stream of a PHP module file.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StreamShifter implements StreamShifterInterface
{
    private bool $shifting = false;

    public function __construct(
        private readonly ShiftSetResolverInterface $shiftSetResolver,
        private readonly ShiftSetShifterInterface $shiftSetShifter
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getShiftSetShifter(): ShiftSetShifterInterface
    {
        return $this->shiftSetShifter;
    }

    /**
     * @param resource $resource
     * @return resource
     */
    public function shift(string $path, $resource)
    {
        $shiftSet = $this->shiftSetResolver->resolveShiftSet($path);

        /*
         * Early-out if no shifts apply to this path.
         *
         * Don't attempt to perform shifts while we're already in the process
         * of shifting a file, to prevent recursion.
         */
        if ($shiftSet === null || $this->shifting === true) {
            return $resource;
        }

        $this->shifting = true;

        /*
         * File should have one or more shifts applied:
         *
         * - Read its entire contents into memory,
         * - Apply all shifts
         * - Write the shifted contents to an in-memory buffer
         * - Use the in-memory buffer as the backing buffer for this stream,
         *   so that the shifted contents are treated as the contents of the file.
         *   Note that the original file is not modified in any way.
         */
        $contents = stream_get_contents($resource);

        try {
            $shiftedContents = $this->shiftSetShifter->shift($contents, $shiftSet);
        } finally {
            $this->shifting = false;
        }

        $resource = fopen('php://memory', 'wb+');
        fwrite($resource, $shiftedContents);
        rewind($resource);

        return $resource;
    }
}
