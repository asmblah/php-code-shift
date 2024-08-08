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

namespace Asmblah\PhpCodeShift\Tests\Functional\Shift\Shift;

use Asmblah\PhpCodeShift\CodeShift;
use Asmblah\PhpCodeShift\Shifter\Filter\FileFilter;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\ClassHook\ClassHookShiftSpec;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;

/**
 * Class ClassHookShiftTypeTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ClassHookShiftTypeTest extends AbstractTestCase
{
    private CodeShift $codeShift;

    public function setUp(): void
    {
        $this->codeShift = new CodeShift();

        /** @var class-string $className */
        $className = 'My\Stuff\MyClass';

        /** @var class-string $replacementClassName */
        $replacementClassName = get_class(new class {
            public const MY_CONST = 'my replacement const';

            public function getViaInstanceMethod(): string
            {
                return 'my replacement string from instance method';
            }

            public static function getViaStaticMethod(): string
            {
                return 'my replacement string from static method';
            }
        });

        $this->codeShift->shift(
            new ClassHookShiftSpec(
                $className,
                $replacementClassName
            ),
            new FileFilter(dirname(__DIR__, 2) . '/Fixtures/**')
        );
    }

    public function tearDown(): void
    {
        $this->codeShift->uninstall();
    }

    public function testCanHookClassInModuleRootWhenInGlobalNamespace(): void
    {
        $result = include __DIR__ . '/../../Fixtures/hook/class/class_module_root_global_namespace_test.php';

        static::assertEquals(
            [
                'getViaInstanceMethod()' => 'my replacement string from instance method',
                '::MY_CONST' => 'my replacement const',
                'getViaStaticMethod()' => 'my replacement string from static method',
            ],
            $result
        );
    }

    public function testCanHookClassSplitAcrossMultipleLinesInModuleRootWhenInGlobalNamespace(): void
    {
        $result = include __DIR__ . '/../../Fixtures/hook/class/class_multi_line_test.php';

        static::assertEquals(
            [
                'getViaInstanceMethod()' => 'my replacement string from instance method',
                '::MY_CONST' => 'my replacement const',
                'getViaStaticMethod()' => 'my replacement string from static method',
            ],
            $result
        );
    }

    public function testCanHookClassInModuleRootWhenInsideNamespace(): void
    {
        $result = include __DIR__ . '/../../Fixtures/hook/class/class_module_root_namespace_test.php';

        static::assertEquals(
            [
                'getViaInstanceMethod()' => 'my replacement string from instance method',
                '::MY_CONST' => 'my replacement const',
                'getViaStaticMethod()' => 'my replacement string from static method',
            ],
            $result
        );
    }
}
