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
use Asmblah\PhpCodeShift\Shifter\Filter\MultipleFilter;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\String\StringShiftSpec;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;

/**
 * Class NonIncludeFilesystemAccessTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class NonIncludeFilesystemAccessTest extends AbstractTestCase
{
    private ?CodeShift $codeShift;
    private ?string $varPath;

    public function setUp(): void
    {
        $this->varPath = __DIR__ . '/../../var';
        @mkdir($this->varPath, recursive: true);

        $this->codeShift = new CodeShift();

        $this->codeShift->shift(
            new StringShiftSpec(
                'mystring',
                'yourstring'
            ),
            new MultipleFilter([
                new FileFilter(__DIR__ . '/Fixtures/**'),
                new FileFilter($this->varPath . '/**'),
            ])
        );
    }

    public function tearDown(): void
    {
        $this->codeShift->uninstall();

        $this->rimrafDescendantsOf($this->varPath);
    }

    private function rimrafDescendantsOf(string $path): void
    {
        foreach (glob($path . '/**') as $subPath) {
            if (is_file($subPath)) {
                unlink($subPath);
            } else {
                $this->rimrafDescendantsOf($subPath);

                rmdir($subPath);
            }
        }
    }

    public function testNonIncludeReadsAndWritesShouldNotBeAffected(): void
    {
        file_put_contents($this->varPath . '/my_written_script.php', '<?php return "this is mystring";');

        $result = file_get_contents($this->varPath . '/my_written_script.php');

        // Contents should not be modified despite there being a shift configured.
        static::assertSame('<?php return "this is mystring";', $result);
    }

    public function testParseIniFileIsNotAffected(): void
    {
        // May seem irrelevant, but see notes in StreamHandler->streamOpen(...).
        $result = parse_ini_file(__DIR__ . '/Fixtures/ini_file.ini', true);

        // Contents should not be modified despite there being a shift configured.
        static::assertEquals(
            [
                'my_section' => [
                    'my_value' => 'mystring is here',
                ],
            ],
            $result
        );
    }
}
