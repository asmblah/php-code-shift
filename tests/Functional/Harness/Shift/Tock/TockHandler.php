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
    private static array $calls = [];

    /**
     * @return array<string>
     */
    public static function getCalls(): array
    {
        return self::$calls;
    }

    public static function reset(): void
    {
        self::$calls = [];
    }

    public static function tock(): void
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        self::$calls[] = $backtrace[1]['function'];
    }
}
