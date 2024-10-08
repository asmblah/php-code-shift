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

namespace Asmblah\PhpCodeShift\Shifter\Stream\Native;

use Asmblah\PhpCodeShift\Exception\NoWrappedResourceAvailableException;
use Asmblah\PhpCodeShift\Shifter\Stream\Handler\StreamHandlerInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\StreamWrapperManager;

/**
 * Class StreamWrapper.
 *
 * Hooks filesystem operations (fopen(), require[_once](...) etc.) to allow shifts to be applied.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StreamWrapper implements StreamWrapperInterface
{
    public const PROTOCOLS = ['file', 'phar'];

    /**
     * @var resource|null
     */
    public $context = null;
    private bool $isInclude = false;
    private static bool $isRegistered = false;
    private ?string $mode = null;
    private ?string $path = null;
    private StreamHandlerInterface $streamHandler;
    /**
     * @var resource|null
     */
    private $wrappedResource = null;

    public function __construct()
    {
        // Stream wrapper classes are instantiated by the PHP engine for each opened stream,
        // so we cannot inject dependencies into this constructor. Instead, we fetch them statically.
        $this->streamHandler = StreamWrapperManager::getStreamHandler();
    }

    public function dir_closedir(): bool
    {
        if (!$this->wrappedResource) {
            return false;
        }

        $result = $this->streamHandler->closeDir($this);

        $this->path = null;
        $this->wrappedResource = null;

        return $result;
    }

    public function dir_opendir(string $path, int $options): bool
    {
        $result = $this->streamHandler->openDir($this, $path, $options);

        if ($result !== null) {
            $this->path = $path;
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

        return $this->streamHandler->readDir($this);
    }

    public function dir_rewinddir(): bool
    {
        if (!$this->wrappedResource) {
            return false;
        }

        return $this->streamHandler->rewindDir($this);
    }

    /**
     * @inheritDoc
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @inheritDoc
     */
    public function getOpenMode(): string
    {
        if ($this->mode === null) {
            throw new NoWrappedResourceAvailableException();
        }

        return $this->mode;
    }

    /**
     * @inheritDoc
     */
    public function getOpenPath(): string
    {
        if ($this->path === null) {
            throw new NoWrappedResourceAvailableException();
        }

        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function getWrappedResource()
    {
        if ($this->wrappedResource === null) {
            throw new NoWrappedResourceAvailableException();
        }

        return $this->wrappedResource;
    }

    /**
     * @inheritDoc
     */
    public function isInclude(): bool
    {
        return $this->isInclude;
    }

    /**
     * Determines whether the stream wrapper has been registered.
     */
    public static function isRegistered(): bool
    {
        return self::$isRegistered;
    }

    public function mkdir(string $path, int $mode, int $options): bool
    {
        return $this->streamHandler->mkdir($this, $path, $mode, $options);
    }

    /**
     * Registers this stream wrapper.
     */
    public static function register(): void
    {
        if (self::$isRegistered) {
            return;
        }

        foreach (static::PROTOCOLS as $protocol) {
            stream_wrapper_unregister($protocol);
            stream_wrapper_register($protocol, static::class);
        }

        self::$isRegistered = true;
    }

    public function rename(string $fromPath, string $toPath): bool
    {
        return $this->streamHandler->rename($this, $fromPath, $toPath);
    }

    public function rmdir(string $path, int $options): bool
    {
        return $this->streamHandler->rmdir($this, $path, $options);
    }

    /**
     * @return resource|false
     */
    public function stream_cast(int $cast_as)
    {
        if (!$this->wrappedResource) {
            return false;
        }

        return $this->streamHandler->streamCast($this, $cast_as);
    }

    public function stream_close(): void
    {
        if (!$this->wrappedResource) {
            return;
        }

        $this->streamHandler->streamClose($this);

        $this->path = null;
        $this->wrappedResource = null;
    }

    public function stream_eof(): bool
    {
        if (!$this->wrappedResource) {
            return false;
        }

        return $this->streamHandler->streamEof($this);
    }

    public function stream_flush(): bool
    {
        if (!$this->wrappedResource) {
            return false;
        }

        return $this->streamHandler->streamFlush($this);
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

        return $this->streamHandler->streamLock($this, $operation);
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
        $result = $this->streamHandler->streamOpen($this, $path, $mode, $options, $openedPath);

        if ($result !== null) {
            $this->mode = $mode;
            $this->path = $path;
            $this->isInclude = $result['isInclude'];
            $this->wrappedResource = $result['resource'];

            return true;
        }

        return false;
    }

    public function stream_read(int $count): string|false
    {
        if (!$this->wrappedResource) {
            return false;
        }

        return $this->streamHandler->streamRead($this, $count);
    }

    public function stream_seek(int $offset, int $whence = SEEK_SET): bool
    {
        if (!$this->wrappedResource) {
            return false;
        }

        return $this->streamHandler->streamSeek($this, $offset, $whence);
    }

    /**
     * Changes stream options.
     *
     * @see {@link https://www.php.net/manual/en/streamwrapper.stream-set-option.php}
     */
    public function stream_set_option(int $option, int $arg1, int|null $arg2): bool
    {
        if (!$this->wrappedResource) {
            return false;
        }

        return $this->streamHandler->streamSetOption($this, $option, $arg1, $arg2);
    }

    /**
     * Retrieves information about an open file resource.
     *
     * @see {@link https://www.php.net/manual/en/streamwrapper.stream-stat.php}
     *
     * @return array<mixed>|false
     */
    public function stream_stat(): array|false
    {
        if (!$this->wrappedResource) {
            return false;
        }

        return $this->streamHandler->streamStat($this);
    }

    public function stream_tell(): int|false
    {
        if (!$this->wrappedResource) {
            return false;
        }

        return $this->streamHandler->streamTell($this);
    }

    public function stream_truncate(int $newSize): bool
    {
        if (!$this->wrappedResource) {
            return false;
        }

        return $this->streamHandler->streamTruncate($this, $newSize);
    }

    public function stream_write(string $data): int|false
    {
        if (!$this->wrappedResource) {
            return false;
        }

        return $this->streamHandler->streamWrite($this, $data);
    }

    public function unlink(string $path): bool
    {
        return $this->streamHandler->unlink($this, $path);
    }

    /**
     * Unregisters this stream wrapper if it has been registered.
     */
    public static function unregister(): void
    {
        if (!self::$isRegistered) {
            return;
        }

        foreach (static::PROTOCOLS as $protocol) {
            // Suppress notice "stream_wrapper_restore(): file:// was never changed, nothing to restore".
            @stream_wrapper_restore($protocol);
        }

        self::$isRegistered = false;
    }

    /**
     * Retrieves information about a file from its path.
     *
     * @see {@link https://www.php.net/manual/en/streamwrapper.url-stat.php}
     *
     * @return array<mixed>|false
     */
    public function url_stat(string $path, int $flags): array|false
    {
        return $this->streamHandler->urlStat($path, $flags);
    }
}
