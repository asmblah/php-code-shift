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

namespace Asmblah\PhpCodeShift\Logger;

use Psr\Log\AbstractLogger as PsrAbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Stringable;

/**
 * Class DelegatingLogger.
 *
 * Allows the logger to be changed at runtime, such as by nytris/shift-symfony.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class DelegatingLogger extends PsrAbstractLogger implements DelegatingLoggerInterface
{
    private LoggerInterface $innerLogger;

    public function __construct()
    {
        $this->innerLogger = new NullLogger();
    }

    /**
     * @inheritDoc
     */
    public function getInnerLogger(): LoggerInterface
    {
        return $this->innerLogger;
    }

    /**
     * @inheritDoc
     */
    public function setInnerLogger(LoggerInterface $innerLogger): void
    {
        $this->innerLogger = $innerLogger;
    }

    /**
     * @inheritDoc
     *
     * Note that $message is untyped for compatibility with psr/log v1 as well as v2+.
     *
     * @param Stringable|string $message
     * @param array<mixed> $context
     */
    public function log($level, /*Stringable|string*/ $message, array $context = []): void
    {
        $this->innerLogger->log($level, $message, $context);
    }
}
