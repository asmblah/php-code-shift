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
 * Class Posix.
 *
 * Abstraction over POSIX stdlib.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Posix implements PosixInterface
{
    /**
     * @inheritDoc
     */
    public function getGroupId(): int
    {
        return posix_getegid();
    }

    /**
     * @inheritDoc
     */
    public function getGroupSet(): array
    {
        return posix_getgroups();
    }

    /**
     * @inheritDoc
     */
    public function getUserId(): int
    {
        return posix_geteuid();
    }

    /**
     * @inheritDoc
     */
    public function isPosixAvailable(): bool
    {
        return extension_loaded('posix');
    }
}
