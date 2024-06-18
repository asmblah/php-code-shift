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

namespace Asmblah\PhpCodeShift\Posix;

/**
 * Interface PosixInterface.
 *
 * Abstraction over POSIX stdlib.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface PosixInterface
{
    /**
     * Fetches the group ID of the current process.
     */
    public function getGroupId(): int;

    /**
     * Fetches the group IDs of the group set of the current process.
     *
     * @return int[]
     */
    public function getGroupSet(): array;

    /**
     * Fetches the user ID of the current process.
     */
    public function getUserId(): int;

    /**
     * Determines whether the POSIX extension is available.
     */
    public function isPosixAvailable(): bool;
}
