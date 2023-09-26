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

namespace Asmblah\PhpCodeShift\Shifter\Printer;

/**
 * Class PrintedNodeCollection.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class PrintedNodeCollection implements PrintedNodeCollectionInterface
{
    /**
     * @param PrintedNodeInterface[] $printedNodes
     */
    public function __construct(
        private readonly array $printedNodes,
        private readonly int $startLine,
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
    public function getPrintedNodes(): array
    {
        return $this->printedNodes;
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
    public function join(string $delimiter): string
    {
        return implode(
            $delimiter,
            array_map(
                static function (PrintedNodeInterface $printedNode) {
                    return $printedNode->getCode();
                },
                $this->printedNodes
            )
        );
    }
}
