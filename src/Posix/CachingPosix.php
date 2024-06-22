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

namespace Asmblah\PhpCodeShift\Posix;

/**
 * Class CachingPosix.
 *
 * Abstraction over POSIX stdlib that caches results for improved performance.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class CachingPosix implements PosixInterface
{
    private int|null $groupId = null;
    /**
     * @var int[]|null
     */
    private array|null $groupSet = null;
    private bool|null $isPosixAvailable = null;
    private int|null $userId = null;

    public function __construct(
        private readonly PosixInterface $posix
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getGroupId(): int
    {
        if ($this->groupId === null) {
            $this->groupId = $this->posix->getGroupId();
        }

        return $this->groupId;
    }

    /**
     * @inheritDoc
     */
    public function getGroupSet(): array
    {
        if ($this->groupSet === null) {
            $this->groupSet = $this->posix->getGroupSet();
        }

        return $this->groupSet;
    }

    /**
     * @inheritDoc
     */
    public function getUserId(): int
    {
        if ($this->userId === null) {
            $this->userId = $this->posix->getUserId();
        }

        return $this->userId;
    }

    /**
     * @inheritDoc
     */
    public function isPosixAvailable(): bool
    {
        if ($this->isPosixAvailable === null) {
            $this->isPosixAvailable = $this->posix->isPosixAvailable();
        }

        return $this->isPosixAvailable;
    }
}
