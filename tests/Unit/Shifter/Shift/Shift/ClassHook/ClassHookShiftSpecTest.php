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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter\Shift\Shift\ClassHook;

use Asmblah\PhpCodeShift\Shifter\Hook\ClassHooks;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\ClassHook\ClassHookShiftSpec;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;

/**
 * Class ClassHookShiftSpecTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ClassHookShiftSpecTest extends AbstractTestCase
{
    private ClassHookShiftSpec $shiftSpec;

    public function setUp(): void
    {
        ClassHooks::clear();

        /** @var class-string $className */
        $className = 'My\Stuff\MyClass';
        /** @var class-string $replacementClassName */
        $replacementClassName = 'Your\Stuff\YourClass';

        $this->shiftSpec = new ClassHookShiftSpec(
            $className,
            $replacementClassName
        );
    }

    public function tearDown(): void
    {
        ClassHooks::clear();
    }

    public function testGetClassNameFetchesTheOriginalClassName(): void
    {
        static::assertSame('My\Stuff\MyClass', $this->shiftSpec->getClassName());
    }

    public function testGetReplacementClassNameFetchesTheReplacementClassName(): void
    {
        static::assertSame('Your\Stuff\YourClass', $this->shiftSpec->getReplacementClassName());
    }

    public function testInitInstallsTheClassHook(): void
    {
        /** @var class-string $className */
        $className = 'My\Stuff\MyClass';

        $this->shiftSpec->init();

        static::assertSame('Your\Stuff\YourClass', ClassHooks::getReplacement($className));
    }
}
