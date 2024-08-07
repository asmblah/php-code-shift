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

namespace Asmblah\PhpCodeShift;

use Asmblah\PhpCodeShift\Cache\CacheInterface;
use Nytris\Core\Package\PackageFacadeInterface;
use Psr\Log\LoggerInterface;

/**
 * Interface ShiftInterface.
 *
 * Defines the public facade API for the library.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface ShiftInterface extends PackageFacadeInterface
{
    /**
     * Fetches the cache.
     */
    public function getCache(): CacheInterface;

    /**
     * Fetches the configured logger. Will be a NullLogger if none.
     */
    public function getLogger(): LoggerInterface;

    /**
     * Sets a new logger to use.
     */
    public function setLogger(LoggerInterface $logger): void;
}
