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

namespace Asmblah\PhpCodeShift\Tests\Functional;

use Asmblah\PhpCodeShift\CodeShift;
use Asmblah\PhpCodeShift\Shifter\Filter\FileFilter;
use Asmblah\PhpCodeShift\Shifter\Shift\Spec\FunctionHookShiftSpec;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;

/**
 * Class FileFilteringTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class FileFilteringTest extends AbstractTestCase
{
    private ?CodeShift $codeShift;

    public function setUp(): void
    {
        $this->codeShift = new CodeShift();
    }

    public function tearDown(): void
    {
        $this->codeShift->uninstall();
    }

    public function testSupportsFilteringForAllPhpFiles(): void
    {
        $this->codeShift->shift(
            new FunctionHookShiftSpec(
                'substr',
                function (callable $originalSubstr) {
                    return function (string $string, int $offset, ?int $length = null) use ($originalSubstr) {
                        return '[substr<' . $originalSubstr($string, $offset, $length) . '>]';
                    };
                }
            ),
            new FileFilter('**/*.php')
        );

        try {
            $result = include __DIR__ . '/Fixtures/all_php_files_filter.php';
        } finally {
            /*
             * Ensure that if filtering of PHP Code Shift internals fails,
             * we don't cause internal issues inside PHPUnit for example
             * (previously, this test was being erroneously marked as passed).
             */
            $this->codeShift->uninstall();
        }

        static::assertSame('[substr<ll>]', $result);
    }
}
