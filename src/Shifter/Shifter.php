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

namespace Asmblah\PhpCodeShift\Shifter;

use Asmblah\PhpCodeShift\Shifter\Shift\ShiftCollectionInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\ShiftInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\StreamWrapperManager;

/**
 * Class Shifter.
 *
 * Wraps a collection of shifts that may be installed and applied.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Shifter implements ShifterInterface
{
    private bool $installed = false;

    public function __construct(private readonly ShiftCollectionInterface $shiftCollection)
    {
    }

    /**
     * @inheritDoc
     */
    public function addShift(ShiftInterface $shift): void
    {
        $this->shiftCollection->addShift($shift);

        $shift->init();
    }

    /**
     * @inheritDoc
     */
    public function install(): void
    {
        StreamWrapperManager::installShiftCollection($this->shiftCollection);

        $this->installed = true;
    }

    /**
     * @inheritDoc
     */
    public function isInstalled(): bool
    {
        return $this->installed;
    }

    /**
     * @inheritDoc
     */
    public function uninstall(): void
    {
        StreamWrapperManager::uninstallShiftCollection($this->shiftCollection);

        $this->shiftCollection->clear();

        $this->installed = false;
    }
}
