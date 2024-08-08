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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter\Hook;

use Asmblah\PhpCodeShift\Shifter\Hook\ClassHooks;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;

/**
 * Class ClassHooksTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ClassHooksTest extends AbstractTestCase
{
    public function setUp(): void
    {
        ClassHooks::clear();
    }

    public function tearDown(): void
    {
        ClassHooks::clear();
    }

    public function testInstallHookInstallsAHook(): void
    {
        /** @var class-string $className */
        $className = 'MyClass';
        /** @var class-string $replacementClassName */
        $replacementClassName = 'YourClass';

        ClassHooks::installHook($className, $replacementClassName);

        static::assertSame($replacementClassName, ClassHooks::getReplacement($className));
    }
}