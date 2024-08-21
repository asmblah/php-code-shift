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

namespace Asmblah\PhpCodeShift\Tests\Functional\Harness\StreamHandler;

use Asmblah\PhpCodeShift\Shifter\Stream\Handler\AbstractStreamHandlerDecorator;
use Asmblah\PhpCodeShift\Shifter\Stream\Handler\StreamHandlerInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Native\StreamWrapperInterface;
use Asmblah\PhpCodeShift\Util\CallStackInterface;

/**
 * Class CallStackTestStreamHandler.
 *
 * Used by CallStackTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class CallStackTestStreamHandler extends AbstractStreamHandlerDecorator
{
    private ?string $nativeFunctionName = null;

    public function __construct(
        StreamHandlerInterface $wrappedStreamHandler,
        private readonly CallStackInterface $callStack
    ) {
        parent::__construct($wrappedStreamHandler);
    }

    /**
     * Fetches the name of the native function that most recently called into this handler.
     */
    public function getNativeFunctionName(): string
    {
        return $this->nativeFunctionName;
    }

    /**
     * @inheritDoc
     */
    public function streamOpen(
        StreamWrapperInterface $streamWrapper,
        string $path,
        string $mode,
        int $options,
        ?string &$openedPath
    ): ?array {
        if ($this->nativeFunctionName === null) {
            $this->nativeFunctionName = $this->callStack->getNativeFunctionName();
        }

        return parent::streamOpen($streamWrapper, $path, $mode, $options, $openedPath);
    }

    /**
     * @inheritDoc
     */
    public function urlStat(string $path, int $flags): array|false
    {
        if ($this->nativeFunctionName === null) {
            $this->nativeFunctionName = $this->callStack->getNativeFunctionName();
        }

        return parent::urlStat($path, $flags);
    }
}
