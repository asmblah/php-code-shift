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
use Asmblah\PhpCodeShift\Shift;
use Asmblah\PhpCodeShift\Shifter\Filter\FileFilter;
use Asmblah\PhpCodeShift\Shifter\Filter\MultipleFilter;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\String\StringLiteralShiftSpec;
use Asmblah\PhpCodeShift\Shifter\Stream\Handler\AbstractStreamHandlerDecorator;
use Asmblah\PhpCodeShift\Shifter\Stream\Handler\StreamHandlerInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Native\StreamWrapperInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\StreamWrapperManager;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;

/**
 * Class StreamWrapperTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StreamWrapperTest extends AbstractTestCase
{
    private CodeShift $codeShift;
    public ?StreamWrapperInterface $openStreamWrapper = null;
    private StreamHandlerInterface $streamHandler;
    private string $varPath;

    public function setUp(): void
    {
        $this->varPath = dirname(__DIR__, 2) . '/var/test';
        @mkdir($this->varPath, recursive: true);

        Shift::uninstall();

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

        // Install a custom StreamHandler to allow capture of the stream wrapper used for includes.
        $this->streamHandler = new class(
            StreamWrapperManager::getStreamHandler(),
            $this
        ) extends AbstractStreamHandlerDecorator {
            public function __construct(
                StreamHandlerInterface $wrappedStreamHandler,
                private readonly StreamWrapperTest $test
            ) {
                parent::__construct($wrappedStreamHandler);
            }

            /**
             * @inheritDoc
             */
            public function streamOpen(StreamWrapperInterface $streamWrapper, string $path, string $mode, int $options, ?string &$openedPath): ?array
            {
                if (str_contains($path, 'my_written_script.php')) {
                    $this->test->openStreamWrapper = $streamWrapper;
                }

                return parent::streamOpen($streamWrapper, $path, $mode, $options, $openedPath);
            }
        };
        StreamWrapperManager::setStreamHandler($this->streamHandler);
    }

    public function tearDown(): void
    {
        Shift::uninstall();
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

    public function testIncludeStreamsShouldBeMarkedAsSuch(): void
    {
        file_put_contents($this->varPath . '/my_written_script.php', '<?php return "this is mystring";');

        include $this->varPath . '/my_written_script.php';

        static::assertInstanceOf(StreamWrapperInterface::class, $this->openStreamWrapper);
        static::assertTrue($this->openStreamWrapper->isInclude());
    }

    public function testNonIncludeStreamsShouldBeMarkedAsSuch(): void
    {
        file_put_contents($this->varPath . '/my_written_script.php', '<?php return "this is mystring";');

        $stream = fopen($this->varPath . '/my_written_script.php', 'rb');
        $metaData = stream_get_meta_data($stream);

        static::assertSame('user-space', $metaData['wrapper_type']);
        /** @var StreamWrapperInterface $streamWrapper */
        $streamWrapper = $metaData['wrapper_data'];
        static::assertInstanceOf(StreamWrapperInterface::class, $streamWrapper);
        static::assertFalse($streamWrapper->isInclude());
    }
}
