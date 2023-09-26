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

namespace Asmblah\PhpCodeShift\Shifter\Shift\Context;

/**
 * Class ModificationContext.
 *
 * Represents the current context of the modification.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ModificationContext implements ModificationContextInterface
{
    private string $contents;
    private int $delta = 0;

    public function __construct(
        private ShiftContextInterface $shiftContext
    ) {
        $this->contents = $this->shiftContext->getContents();
    }

    /**
     * @inheritDoc
     */
    public function getContents(): string
    {
        return $this->contents;
    }

    /**
     * @inheritDoc
     */
    public function getDelta(): int
    {
        return $this->delta;
    }

    /**
     * @inheritDoc
     */
    public function getPath(): string
    {
        return $this->shiftContext->getPath();
    }

    /**
     * @inheritDoc
     */
    public function setContents(string $contents): void
    {
        $this->contents = $contents;
    }

    /**
     * @inheritDoc
     */
    public function setDelta(int $delta): void
    {
        $this->delta = $delta;
    }
}
