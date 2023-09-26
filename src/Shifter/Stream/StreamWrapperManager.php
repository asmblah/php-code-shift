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

namespace Asmblah\PhpCodeShift\Shifter\Stream;

use Asmblah\PhpCodeShift\Shared;
use Asmblah\PhpCodeShift\Shifter\Shift\ShiftCollectionInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\ShiftSet;
use Asmblah\PhpCodeShift\Shifter\Shift\ShiftSetInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Handler\StreamHandler;
use Asmblah\PhpCodeShift\Shifter\Stream\Handler\StreamHandlerInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Native\StreamWrapper;
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
     * @var SplObjectStorage<ShiftCollectionInterface, mixed>
     */
    private static SplObjectStorage $shiftCollections;
    private static StreamHandlerInterface $streamHandler;

    public static function init(): void
    {
        /** @var SplObjectStorage<ShiftCollectionInterface, mixed> $shiftCollections */
        $shiftCollections = new SplObjectStorage();

        self::$shiftCollections = $shiftCollections;
        self::$streamHandler = new StreamHandler(Shared::getCallStack(), Shared::getStreamShifter());
    }

    public static function getShiftSetForPath(string $path): ?ShiftSetInterface
    {
        $applicableShifts = [];

        foreach (self::$shiftCollections as $shiftCollection) {
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

    /**
     * Fetches the StreamHandler that the StreamWrapper uses.
     */
    public static function getStreamHandler(): StreamHandlerInterface
    {
        return self::$streamHandler;
    }

    public static function installShiftCollection(ShiftCollectionInterface $shiftCollection): void
    {
        self::$shiftCollections->attach($shiftCollection);

        if (count(self::$shiftCollections) === 1) {
            StreamWrapper::register();
        }
    }

    /**
     * Installs a new StreamHandler to be used for new streams created after this point.
     */
    public static function setStreamHandler(StreamHandlerInterface $streamHandler): void
    {
        self::$streamHandler = $streamHandler;
    }

    public static function uninstallShiftCollection(ShiftCollectionInterface $shiftCollection): void
    {
        self::$shiftCollections->detach($shiftCollection);

        if (count(self::$shiftCollections) === 0) {
            StreamWrapper::unregister();
        }
    }
}
