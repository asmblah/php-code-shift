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

namespace Asmblah\PhpCodeShift\Shifter\Native;

use Asmblah\PhpCodeShift\Exception\NativeFileOperationFailedException;
use Asmblah\PhpCodeShift\Exception\NoWrappedResourceAvailableException;

/**
 * Class StreamWrapper.
 *
 * Hooks filesystem operations (fopen(), require[_once](...) etc.) to allow shifts to be applied.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StreamWrapper
{
    public const PROTOCOL = 'file';

    /**
     * @var resource
     */
    public $context;
    /**
     * @var resource|null
     */
    private $wrappedResource = null;

    public function dir_closedir(): bool
    {
        closedir($this->wrappedResource);
    }

    public function dir_opendir(string $path, int $options): bool
    {
        $resource = $this->unwrapped(
            fn () => $this->context ?
                opendir($path, $this->context) :
                opendir($path)
        );

        if ($resource === false) {
            return false;
        }

        $this->wrappedResource = $resource;

        return true;
    }

    public function dir_readdir(): string
    {
        return readdir($this->wrappedResource);
    }

    public function dir_rewinddir(): bool
    {
        rewinddir($this->wrappedResource);

        return true;
    }

    public function mkdir(string $path, int $mode, int $options): bool
    {
        $recursive = (bool) ($options & STREAM_MKDIR_RECURSIVE);

        return $this->unwrapped(
            fn () => $this->context ?
                mkdir($path, $mode, $recursive, $this->context) :
                mkdir($path, $mode, $recursive)
        );
    }

    public function rename(string $fromPath, string $toPath): bool
    {
        return $this->unwrapped(
            fn () => $this->context ?
                rename($fromPath, $toPath, $this->context) :
                rename($fromPath, $toPath)
        );
    }

    public function rmdir(string $path, int $options): bool
    {
        // TODO: How should $options be handled?

        return $this->unwrapped(
            fn () => $this->context ?
                rmdir($path, $this->context) :
                rmdir($path)
        );
    }

    /**
     * @return resource
     */
    public function stream_cast(int $cast_as)
    {
        // TODO: How should $cast_as be handled?
        //       Safe to ignore as this wrapper should never be used with stream_select()?

        if ($this->wrappedResource === null) {
            throw new NoWrappedResourceAvailableException();
        }

        return $this->wrappedResource;
    }

    public function stream_close(): void
    {
        fclose($this->wrappedResource);

        $this->wrappedResource = null;
    }

    public function stream_eof(): bool
    {
        return feof($this->wrappedResource);
    }

    public function stream_flush(): bool
    {
        return fflush($this->wrappedResource);
    }

    public function stream_lock(int $operation): bool
    {
        return flock($this->wrappedResource, $operation);
    }

    public function stream_metadata(string $path, int $option, mixed $value): bool
    {
        return $this->unwrapped(function () use ($option, $path, $value) {
            switch ($option) {
                case STREAM_META_TOUCH:
                    return touch($path, $value[0] ?? time(), $value[1] ?? time());
                case STREAM_META_OWNER_NAME:
                case STREAM_META_OWNER:
                    return chown($path, $value);
                case STREAM_META_GROUP_NAME:
                case STREAM_META_GROUP:
                    return chgrp($path, $value);
                case STREAM_META_ACCESS:
                    return chmod($path, $value);
                default:
                    return false;
            }
        });
    }

    public function stream_open(
        string $path,
        string $mode,
        int $options,
        ?string &$openedPath
    ): bool {
        $usePath = (bool) ($options & STREAM_USE_PATH);

        $resource = $this->unwrapped(
            fn () => $this->context ?
                fopen($path, $mode, $usePath, $this->context) :
                fopen($path, $mode, $usePath)
        );

        if ($resource === false) {
            return false;
        }

        $shiftSet = StreamWrapperManager::getShiftSetForPath($path);

        if ($shiftSet) {
            /*
             * File should have one or more shifts applied:
             *
             * - Read its entire contents into memory,
             * - Apply all shifts
             * - Write the shifted contents to an in-memory buffer
             * - Use the in-memory buffer as the backing buffer for this stream,
             *   so that the shifted contents are treated as the contents of the file.
             *   Note that the original file is not modified in any way.
             */
            $contents = stream_get_contents($resource);

            $shiftedContents = $shiftSet->shift($contents);

            $resource = fopen('php://memory', 'w+');
            fwrite($resource, $shiftedContents);
            fseek($resource, 0);
        }

        if ($usePath && $openedPath) {
            $openedPath = realpath($path);
        }

        $this->wrappedResource = $resource;

        return true;
    }

    public function stream_read(int $count): string|false
    {
        return fread($this->wrappedResource, $count);
    }

    public function stream_seek(int $offset, int $whence = SEEK_SET): bool
    {
        // fseek(...) returns 0 on success.
        return fseek($this->wrappedResource, $offset, $whence) === 0;
    }

    public function stream_set_option(int $option, int $arg1, int $arg2): bool
    {
        return false; // Not supported for file streams.
    }

    public function stream_stat(): array|false
    {
        return fstat($this->wrappedResource);
    }

    public function stream_tell(): int
    {
        $position = ftell($this->wrappedResource);

        if ($position === false) {
            throw new NativeFileOperationFailedException();
        }

        return $position;
    }

    public function stream_truncate(int $newSize): bool
    {
        return ftruncate($this->wrappedResource, $newSize);
    }

    public function stream_write(string $data): int
    {
        $result = fwrite($this->wrappedResource, $data);

        if ($result === false) {
            throw new NativeFileOperationFailedException();
        }

        return $result;
    }

    public function unlink(string $path): bool
    {
        return $this->unwrapped(
            fn () => $this->context ?
                unlink($path, $this->context) :
                unlink($path)
        );
    }

    public function url_stat(string $path, int $flags): array|false
    {
        // Use lstat(...) for links but stat() for other files.
        $stat = fn () => $flags & STREAM_URL_STAT_LINK ?
            lstat($path) :
            stat($path);

        // Suppress warnings/notices if quiet flag is set.
        return $this->unwrapped(
            $flags & STREAM_URL_STAT_QUIET ?
                fn () => @$stat() :
                $stat
        );
    }

    private function unwrapped(callable $callback): mixed
    {
        stream_wrapper_unregister(static::PROTOCOL);
        stream_wrapper_restore(static::PROTOCOL);

        try {
            return $callback();
        } finally {
            // Note that if we do not unregister again first following the above restore,
            // a segfault will be raised.
            stream_wrapper_unregister(static::PROTOCOL);
            stream_wrapper_register(static::PROTOCOL, static::class);
        }
    }
}
