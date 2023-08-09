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

namespace Asmblah\PhpCodeShift\Shifter\Stream\Native;

use Asmblah\PhpCodeShift\Shifter\Stream\Handler\StreamHandlerInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\StreamWrapperManager;

/**
 * Class StreamWrapper.
 *
 * Hooks filesystem operations (fopen(), require[_once](...) etc.) to allow shifts to be applied.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StreamWrapper
{
    public const PROTOCOLS = ['file', 'phar'];

    /**
     * @var resource
     */
    public $context;
    private StreamHandlerInterface $streamHandler;
    /**
     * @var resource|null
     */
    private $wrappedResource = null;

    public function __construct()
    {
        $this->streamHandler = StreamWrapperManager::getStreamHandler();
    }

    public function dir_closedir(): bool
    {
        if (!$this->wrappedResource) {
            return false;
        }

        return $this->streamHandler->closeDir($this->wrappedResource);
    }

    public function dir_opendir(string $path, int $options): bool
    {
        $result = $this->streamHandler->openDir($this->context, $path, $options);

        if ($result !== null) {
            $this->wrappedResource = $result;

            return true;
        }

        return false;
    }

    public function dir_readdir(): string|false
    {
        if (!$this->wrappedResource) {
            return false;
        }

        return $this->streamHandler->readDir($this->wrappedResource);
    }

    public function dir_rewinddir(): bool
    {
        if (!$this->wrappedResource) {
            return false;
        }

        return $this->streamHandler->rewindDir($this->wrappedResource);
    }

    public function mkdir(string $path, int $mode, int $options): bool
    {
        return $this->streamHandler->mkdir($this->context, $path, $mode, $options);
    }

    public static function register(): void
    {
        foreach (static::PROTOCOLS as $protocol) {
            stream_wrapper_unregister($protocol);
            stream_wrapper_register($protocol, static::class);
        }
    }

    public function rename(string $fromPath, string $toPath): bool
    {
        return $this->streamHandler->rename($this->context, $fromPath, $toPath);
    }

    public function rmdir(string $path, int $options): bool
    {
        return $this->streamHandler->rmdir($this->context, $path, $options);
    }

    /**
     * @return resource|false
     */
    public function stream_cast(int $cast_as)
    {
        if (!$this->wrappedResource) {
            return false;
        }

        return $this->streamHandler->streamCast($this->wrappedResource, $cast_as);
    }

    public function stream_close(): void
    {
        if (!$this->wrappedResource) {
            return;
        }

        $this->streamHandler->streamClose($this->wrappedResource);

        $this->wrappedResource = null;
    }

    public function stream_eof(): bool
    {
        if (!$this->wrappedResource) {
            return false;
        }

        return $this->streamHandler->streamEof($this->wrappedResource);
    }

    public function stream_flush(): bool
    {
        if (!$this->wrappedResource) {
            return false;
        }

        return $this->streamHandler->streamFlush($this->wrappedResource);
    }

    public function stream_lock(int $operation): bool
    {
        if (!$this->wrappedResource) {
            return false;
        }

        if ($operation === 0) {
            // Handle weird scenario where invalid operation is passed.
            $operation = LOCK_EX;
        }

        return $this->streamHandler->streamLock($this->wrappedResource, $operation);
    }

    public function stream_metadata(string $path, int $option, mixed $value): bool
    {
        return $this->streamHandler->streamMetadata($path, $option, $value);
    }

    public function stream_open(
        string $path,
        string $mode,
        int $options,
        ?string &$openedPath
    ): bool {
        $result = $this->streamHandler->streamOpen($this->context, $path, $mode, $options, $openedPath);

        if ($result !== null) {
            $this->wrappedResource = $result;

            return true;
        }

        return false;
    }

    public function stream_read(int $count): string|false
    {
        if (!$this->wrappedResource) {
            return false;
        }

        return $this->streamHandler->streamRead($this->wrappedResource, $count);
    }

    public function stream_seek(int $offset, int $whence = SEEK_SET): bool
    {
        if (!$this->wrappedResource) {
            return false;
        }

        return $this->streamHandler->streamSeek($this->wrappedResource, $offset, $whence);
    }

    public function stream_set_option(int $option, int $arg1, int $arg2): bool
    {
        if (!$this->wrappedResource) {
            return false;
        }

        return $this->streamHandler->streamSetOption($this->wrappedResource, $option, $arg1, $arg2);
    }

    public function stream_stat(): array|false
    {
        if (!$this->wrappedResource) {
            return false;
        }

        return $this->streamHandler->streamStat($this->wrappedResource);
    }

    public function stream_tell(): int|false
    {
        if (!$this->wrappedResource) {
            return false;
        }

        return $this->streamHandler->streamTell($this->wrappedResource);
    }

    public function stream_truncate(int $newSize): bool
    {
        if (!$this->wrappedResource) {
            return false;
        }

        return $this->streamHandler->streamTruncate($this->wrappedResource, $newSize);
    }

    public function stream_write(string $data): int|false
    {
        if (!$this->wrappedResource) {
            return false;
        }

        return $this->streamHandler->streamWrite($this->wrappedResource, $data);
    }

    public function unlink(string $path): bool
    {
        return $this->streamHandler->unlink($this->context, $path);
    }

    public static function unregister(): void
    {
        foreach (static::PROTOCOLS as $protocol) {
            stream_wrapper_unregister($protocol);
            stream_wrapper_restore($protocol);
        }
    }

    public function url_stat(string $path, int $flags): array|false
    {
        return $this->streamHandler->urlStat($path, $flags);
    }
}
