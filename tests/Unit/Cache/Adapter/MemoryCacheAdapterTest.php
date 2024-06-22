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

namespace Asmblah\PhpCodeShift\Tests\Unit\Cache\Adapter;

use Asmblah\PhpCodeShift\Cache\Adapter\MemoryCacheAdapter;
use Asmblah\PhpCodeShift\Exception\FileNotCachedException;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;

/**
 * Class MemoryCacheAdapterTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class MemoryCacheAdapterTest extends AbstractTestCase
{
    private MemoryCacheAdapter $adapter;

    public function setUp(): void
    {
        $this->adapter = new MemoryCacheAdapter();
    }

    public function testHasFileReturnsTrueForCachedFile(): void
    {
        $this->adapter->saveFile('/my/project/root/stuff/my_module.php', '<?php 1234;');

        static::assertTrue($this->adapter->hasFile('/my/project/root/stuff/my_module.php'));
    }

    public function testHasFileReturnsFalseForNonCachedFile(): void
    {
        static::assertFalse($this->adapter->hasFile('/my/project/root/stuff/my_non_existent_module.php'));
    }

    public function testOpenFileRaisesExceptionWhenFileNotCached(): void
    {
        $this->expectException(FileNotCachedException::class);
        $this->expectExceptionMessage('Path not cached: /my/project/root/stuff/my_module.php');

        $this->adapter->openFile('/my/project/root/stuff/my_module.php');
    }

    public function testOpenFileReturnsCachedFileStream(): void
    {
        $this->adapter->saveFile('/my/project/root/stuff/my_module.php', '<?php 4321;');

        $stream = $this->adapter->openFile('/my/project/root/stuff/my_module.php');

        static::assertSame('<?php 4321;', stream_get_contents($stream));
    }

    public function testOpenFileClearsFileFromCacheToAvoidLeakingMemory(): void
    {
        $this->adapter->saveFile('/my/project/root/stuff/my_module.php', '<?php 4321;');

        $this->adapter->openFile('/my/project/root/stuff/my_module.php');

        static::assertFalse($this->adapter->hasFile('/my/project/root/stuff/my_module.php'));
    }

    // ->saveFile(...) is tested above.
}
