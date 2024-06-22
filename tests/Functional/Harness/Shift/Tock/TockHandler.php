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

namespace Asmblah\PhpCodeShift\Tests\Functional\Harness\Shift\Tock;

use Asmblah\PhpCodeShift\Tests\AbstractTestCase;

/**
 * Class TockHandler.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class TockHandler extends AbstractTestCase
{
    /**
     * @var array<string>
     */
    private static array $logs = [];

    /**
     * @return array<string>
     */
    public static function getLogs(): array
    {
        return self::$logs;
    }

    public static function log(string $log): void
    {
        self::$logs[] = 'log() :: ' . $log;
    }

    public static function reset(): void
    {
        self::$logs = [];
    }

    public static function tock(): void
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        self::$logs[] = 'tock() :: ' . $backtrace[1]['function'] . ' @ line ' . $backtrace[0]['line'];
    }
}
