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

namespace Asmblah\PhpCodeShift\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

/**
 * Interface DelegatingLoggerInterface.
 *
 * Allows the logger to be changed at runtime, such as by nytris/shift-symfony.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface DelegatingLoggerInterface extends PsrLoggerInterface
{
    /**
     * Fetches the inner logger.
     */
    public function getInnerLogger(): LoggerInterface;

    /**
     * Sets the inner logger.
     */
    public function setInnerLogger(PsrLoggerInterface $innerLogger): void;
}
