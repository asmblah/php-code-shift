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

namespace Asmblah\PhpCodeShift\Shifter\Resolver;

/**
 * Class CodeModificationExtents.
 *
 * Represents the extents within which a code modification should take place.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class CodeModificationExtents implements CodeModificationExtentsInterface
{
    public function __construct(
        private readonly int $startOffset,
        private readonly int $startLine,
        private readonly int $endOffset,
        private readonly int $endLine
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getEndLine(): int
    {
        return $this->endLine;
    }

    /**
     * @inheritDoc
     */
    public function getEndOffset(): int
    {
        return $this->endOffset;
    }

    /**
     * @inheritDoc
     */
    public function getStartLine(): int
    {
        return $this->startLine;
    }

    /**
     * @inheritDoc
     */
    public function getStartOffset(): int
    {
        return $this->startOffset;
    }
}
