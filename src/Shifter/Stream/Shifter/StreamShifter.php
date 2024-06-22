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

use Asmblah\PhpCodeShift\Cache\Adapter\CacheAdapterInterface;
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
    public function __construct(
        private readonly ShiftSetResolverInterface $shiftSetResolver,
        private readonly ShiftSetShifterInterface $shiftSetShifter,
        private readonly CacheAdapterInterface $cacheAdapter
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
        if ($this->cacheAdapter->hasFile($path)) {
            // Early-out: file has already been shifted, open it from the cache.
            return $this->cacheAdapter->openFile($path);
        }

        $shiftSet = $this->shiftSetResolver->resolveShiftSet($path);

        /*
         * Early-out if no shifts apply to this path.
         */
        if ($shiftSet === null) {
            return $resource;
        }

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

        $shiftedContents = $this->shiftSetShifter->shift($contents, $shiftSet);

        // Cache the shifted contents ready for next time.
        $this->cacheAdapter->saveFile($path, $shiftedContents);

        return $this->cacheAdapter->openFile($path);
    }
}
