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

namespace Asmblah\PhpCodeShift\Shifter\Stream\Handler;

use Asmblah\PhpCodeShift\Shifter\Stream\Native\StreamWrapper;
use Asmblah\PhpCodeShift\Shifter\Stream\StreamWrapperManager;

/**
 * Class StreamHandler.
 *
 * Defines the interface to the low-level filesystem API.
 * May be decorated to hook into filesystem operations.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class StreamHandler implements StreamHandlerInterface
{
    /**
     * @inheritDoc
     */
    public function closeDir($wrappedResource): bool
    {
        closedir($wrappedResource);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function openDir($context, string $path, int $options)
    {
        $resource = $this->unwrapped(
            fn () => $context ?
                opendir($path, $context) :
                opendir($path)
        );

        if ($resource === false) {
            return null;
        }

        return $resource;
    }

    /**
     * @inheritDoc
     */
    public function readDir($wrappedResource): string|false
    {
        return readdir($wrappedResource);
    }

    /**
     * @inheritDoc
     */
    public function rewindDir($wrappedResource): bool
    {
        rewinddir($wrappedResource);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function mkdir($context, string $path, int $mode, int $options): bool
    {
        $recursive = (bool) ($options & STREAM_MKDIR_RECURSIVE);

        return $this->unwrapped(
            fn () => $context ?
                mkdir($path, $mode, $recursive, $context) :
                mkdir($path, $mode, $recursive)
        );
    }

    /**
     * @inheritDoc
     */
    public function rename($context, string $fromPath, string $toPath): bool
    {
        return $this->unwrapped(
            fn () => $context ?
                rename($fromPath, $toPath, $context) :
                rename($fromPath, $toPath)
        );
    }

    /**
     * @inheritDoc
     */
    public function rmdir($context, string $path, int $options): bool
    {
        // TODO: How should $options be handled?

        return $this->unwrapped(
            fn () => $context ?
                rmdir($path, $context) :
                rmdir($path)
        );
    }

    /**
     * @inheritDoc
     */
    public function streamCast($wrappedResource, int $castAs)
    {
        // TODO: How should $castAs be handled?
        //       Safe to ignore as this wrapper should never be used with stream_select()?

        return $wrappedResource;
    }

    /**
     * @inheritDoc
     */
    public function streamClose($wrappedResource): void
    {
        fclose($wrappedResource);
    }

    /**
     * @inheritDoc
     */
    public function streamEof($wrappedResource): bool
    {
        return feof($wrappedResource);
    }

    /**
     * @inheritDoc
     */
    public function streamFlush($wrappedResource): bool
    {
        return fflush($wrappedResource);
    }

    /**
     * @inheritDoc
     */
    public function streamLock($wrappedResource, int $operation): bool
    {
        return flock($wrappedResource, $operation);
    }

    /**
     * @inheritDoc
     */
    public function streamMetadata(string $path, int $option, mixed $value): bool
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

    /**
     * @inheritDoc
     */
    public function streamOpen(
        $context,
        string $path,
        string $mode,
        int $options,
        ?string &$openedPath
    ) {
        $usePath = (bool) ($options & STREAM_USE_PATH);

        $resource = $this->unwrapped(
            fn () => $context ?
                fopen($path, $mode, $usePath, $context) :
                fopen($path, $mode, $usePath)
        );

        if ($resource === false) {
            return null;
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

            $resource = fopen('php://memory', 'wb+');
            fwrite($resource, $shiftedContents);
            fseek($resource, 0);
        }

        if ($usePath && $openedPath) {
            $openedPath = realpath($path);
        }

        return $resource;
    }

    /**
     * @inheritDoc
     */
    public function streamRead($wrappedResource, int $count): string|false
    {
        return fread($wrappedResource, $count);
    }

    /**
     * @inheritDoc
     */
    public function streamSeek($wrappedResource, int $offset, int $whence = SEEK_SET): bool
    {
        // fseek(...) returns 0 on success.
        return fseek($wrappedResource, $offset, $whence) === 0;
    }

    /**
     * @inheritDoc
     */
    public function streamSetOption($wrappedResource, int $option, int $arg1, int $arg2): bool
    {
        return false;

//        // FIXME: Causes segfault?!
//        return $this->unwrapped(
//            fn () => match ($option) {
//                STREAM_OPTION_BLOCKING => stream_set_blocking($this->wrappedResource, (bool)$arg1),
//                STREAM_OPTION_READ_TIMEOUT => stream_set_timeout($this->wrappedResource, $arg1, $arg2),
//                STREAM_OPTION_WRITE_BUFFER => stream_set_write_buffer($this->wrappedResource, $arg1) === 0,
//                STREAM_OPTION_READ_BUFFER => stream_set_read_buffer($this->wrappedResource, $arg1) === 0,
//                default => false,
//            }
//        );
    }

    /**
     * @inheritDoc
     */
    public function streamStat($wrappedResource): array|false
    {
        return fstat($wrappedResource);
    }

    /**
     * @inheritDoc
     */
    public function streamTell($wrappedResource): int|false
    {
        return ftell($wrappedResource);
    }

    /**
     * @inheritDoc
     */
    public function streamTruncate($wrappedResource, int $newSize): bool
    {
        return ftruncate($wrappedResource, $newSize);
    }

    /**
     * @inheritDoc
     */
    public function streamWrite($wrappedResource, string $data): int|false
    {
        return fwrite($wrappedResource, $data);
    }

    /**
     * @inheritDoc
     */
    public function unlink($context, string $path): bool
    {
        return $this->unwrapped(
            fn () => $context ?
                unlink($path, $context) :
                unlink($path)
        );
    }

    /**
     * @inheritDoc
     */
    public function urlStat(string $path, int $flags): array|false
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

    /**
     * @inheritDoc
     */
    public function unwrapped(callable $callback): mixed
    {
        StreamWrapper::unregister();

        try {
            return $callback();
        } finally {
            // Note that if we do not unregister again first following the above restore,
            // a segfault will be raised.
            StreamWrapper::register();
        }
    }
}
