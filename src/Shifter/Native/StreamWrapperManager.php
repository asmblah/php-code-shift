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

namespace Asmblah\PhpCodeShift\Shifter\Native;

use Asmblah\PhpCodeShift\Shifter\Shift\ShiftCollectionInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\ShiftSet;
use Asmblah\PhpCodeShift\Shifter\Shift\ShiftSetInterface;
use SplObjectStorage;

/**
 * Class StreamWrapperManager.
 *
 * Manages the interaction between the stream wrapper API (which is class-based
 * and therefore data must be stored statically) and the DI-based rest of this library.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StreamWrapperManager
{
    /**
     * @var SplObjectStorage<ShiftCollectionInterface>|null
     */
    private static ?SplObjectStorage $shiftCollections = null;

    public static function init(): void
    {
        if (static::$shiftCollections === null) {
            static::$shiftCollections = new SplObjectStorage();
        }
    }

    public static function getShiftSetForPath(string $path): ?ShiftSetInterface
    {
        $applicableShifts = [];

        foreach (static::$shiftCollections as $shiftCollection) {
            foreach ($shiftCollection->getShifts() as $shift) {
                if ($shift->appliesTo($path)) {
                    $applicableShifts[] = $shift;
                }
            }
        }

        return count($applicableShifts) > 0 ?
            new ShiftSet($path, $applicableShifts) :
            null;
    }

    public static function installShiftCollection(ShiftCollectionInterface $shiftCollection): void
    {
        static::$shiftCollections->attach($shiftCollection);

        if (count(static::$shiftCollections) === 1) {
            stream_wrapper_unregister(StreamWrapper::PROTOCOL);
            stream_wrapper_register(StreamWrapper::PROTOCOL, StreamWrapper::class);
        }
    }

    public static function uninstallShiftCollection(ShiftCollectionInterface $shiftCollection): void
    {
        static::$shiftCollections->detach($shiftCollection);

        if (count(static::$shiftCollections) === 0) {
            stream_wrapper_unregister(StreamWrapper::PROTOCOL);
            stream_wrapper_restore(StreamWrapper::PROTOCOL);
        }
    }
}
