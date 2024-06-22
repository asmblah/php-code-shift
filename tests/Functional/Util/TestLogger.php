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

namespace Asmblah\PhpCodeShift\Tests\Functional\Util;

use Psr\Log\AbstractLogger;
use Stringable;

/**
 * Class TestLogger.
 *
 * Logger that is solely used during functional testing.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class TestLogger extends AbstractLogger
{
    /**
     * @var array<array<mixed>>
     */
    private array $logs = [];

    /**
     * Fetches the recorded logs.
     *
     * @return array<array<mixed>>
     */
    public function getLogs(): array
    {
        return $this->logs;
    }

    /**
     * @inheritDoc
     *
     * @param array<mixed> $context
     */
    public function log($level, Stringable|string $message, array $context = []): void
    {
        $this->logs[] = [$level, $message, $context];
    }
}
