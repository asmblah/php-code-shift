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

namespace Asmblah\PhpCodeShift\Tests\Functional;

use Asmblah\PhpCodeShift\CodeShift;
use Asmblah\PhpCodeShift\Shifter\Filter\FileFilter;
use Asmblah\PhpCodeShift\Shifter\Filter\MultipleFilter;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\String\StringLiteralShiftSpec;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use ErrorException;
use SplFileInfo;

/**
 * Class NonIncludeFilesystemAccessTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class NonIncludeFilesystemAccessTest extends AbstractTestCase
{
    private CodeShift $codeShift;
    private string $varPath;

    public function setUp(): void
    {
        $this->varPath = dirname(__DIR__, 2) . '/var/test';
        @mkdir($this->varPath, recursive: true);

        $this->codeShift = new CodeShift();

        $this->codeShift->shift(
            new StringLiteralShiftSpec(
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

    // See StreamWrapperTest for `->isInclude()` checking.

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

    public function testStatOfNonExistentFileIsHandledCorrectlyBySplFileInfoConstructor(): void
    {
        $myStreamWrapperClass = get_class(new class {
            public static bool $exists = false;

            public function stream_open(
                string $path,
                string $mode,
                int $options,
                ?string &$openedPath
            ): bool {
                $openedPath = $path;

                self::$exists = file_exists($path);

                return true;
            }
        });
        stream_wrapper_register('myproto', $myStreamWrapperClass);

        $splFileInfo = new SplFileInfo(__DIR__ . '/non_existent.txt');

        static::assertFalse($splFileInfo->isFile());
        static::assertFalse($myStreamWrapperClass::$exists);
    }

    public function testStatOfNonExistentFileIsHandledCorrectlyByIsFileBuiltin(): void
    {
        static::assertFalse(is_file(__DIR__ . '/non_existent.txt'));
    }

    public function testStatOfNonExistentFileIsHandledCorrectlyByIsFileBuiltinWhenCustomErrorHandlerInstalled(): void
    {
        // Force "stat(): stat failed for [...]/php-code-shift/tests/Functional/non_existent.txt"
        // to be thrown if applicable rather than be suppressed by the "@" suppression operator.
        set_error_handler(static function (int $errorCode, string $errorMessage) {
            throw new ErrorException($errorMessage, $errorCode);
        });

        try {
            static::assertFalse(is_file(__DIR__ . '/non_existent.txt'));
        } finally {
            restore_error_handler();
        }
    }

    public function testStreamOptionsMayBeSet(): void
    {
        $path = $this->varPath . '/my.txt';
        file_put_contents($path, 'My text');
        $stream = fopen($path, 'rb');

        stream_set_blocking($stream, true);
        stream_set_timeout($stream, 10);
        stream_set_write_buffer($stream, 1024);
        stream_set_read_buffer($stream, 1024);

        static::assertTrue(stream_get_meta_data($stream)['blocked']);
    }
}
