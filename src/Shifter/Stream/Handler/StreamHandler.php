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

namespace Asmblah\PhpCodeShift\Shifter\Stream\Handler;

use Asmblah\PhpCodeShift\Filesystem\Stat\StatResolverInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Native\StreamWrapperInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Shifter\StreamShifterInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Unwrapper\UnwrapperInterface;
use Asmblah\PhpCodeShift\Util\CallStackInterface;

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
    public function __construct(
        private readonly CallStackInterface $callStack,
        private readonly StreamShifterInterface $streamShifter,
        private readonly UnwrapperInterface $unwrapper,
        private readonly StatResolverInterface $statResolver
    ) {
    }

    /**
     * @inheritDoc
     */
    public function closeDir(StreamWrapperInterface $streamWrapper): bool
    {
        closedir($streamWrapper->getWrappedResource());

        return true;
    }

    /**
     * @inheritDoc
     */
    public function isInclude(int $streamOpenOptions): bool
    {
        /*
         * Include determination logic inspired by Patchwork's.
         *
         * @see {@link https://github.com/antecedent/patchwork/blob/master/src/CodeManipulation/Stream.php}
         */
        $including = (bool) ($streamOpenOptions & self::STREAM_OPEN_FOR_INCLUDE);

        // In PHP 7 and 8, `parse_ini_file()` also sets STREAM_OPEN_FOR_INCLUDE.
        if ($including && $this->callStack->getNativeFunctionName() === 'parse_ini_file') {
            $including = false;
        }

        return $including;
    }

    /**
     * @inheritDoc
     */
    public function openDir(StreamWrapperInterface $streamWrapper, string $path, int $options)
    {
        $context = $streamWrapper->getContext();

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
    public function readDir(StreamWrapperInterface $streamWrapper): string|false
    {
        return readdir($streamWrapper->getWrappedResource());
    }

    /**
     * @inheritDoc
     */
    public function rewindDir(StreamWrapperInterface $streamWrapper): bool
    {
        rewinddir($streamWrapper->getWrappedResource());

        return true;
    }

    /**
     * @inheritDoc
     */
    public function mkdir(StreamWrapperInterface $streamWrapper, string $path, int $mode, int $options): bool
    {
        $context = $streamWrapper->getContext();
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
    public function rename(StreamWrapperInterface $streamWrapper, string $fromPath, string $toPath): bool
    {
        $context = $streamWrapper->getContext();

        return $this->unwrapped(
            fn () => $context ?
                rename($fromPath, $toPath, $context) :
                rename($fromPath, $toPath)
        );
    }

    /**
     * @inheritDoc
     */
    public function rmdir(StreamWrapperInterface $streamWrapper, string $path, int $options): bool
    {
        $context = $streamWrapper->getContext();

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
    public function streamCast(StreamWrapperInterface $streamWrapper, int $castAs)
    {
        // TODO: How should $castAs be handled?
        //       Safe to ignore as this wrapper should never be used with stream_select()?

        return $streamWrapper->getWrappedResource();
    }

    /**
     * @inheritDoc
     */
    public function streamClose(StreamWrapperInterface $streamWrapper): void
    {
        fclose($streamWrapper->getWrappedResource());
    }

    /**
     * @inheritDoc
     */
    public function streamEof(StreamWrapperInterface $streamWrapper): bool
    {
        return feof($streamWrapper->getWrappedResource());
    }

    /**
     * @inheritDoc
     */
    public function streamFlush(StreamWrapperInterface $streamWrapper): bool
    {
        return fflush($streamWrapper->getWrappedResource());
    }

    /**
     * @inheritDoc
     */
    public function streamLock(StreamWrapperInterface $streamWrapper, int $operation): bool
    {
        return flock($streamWrapper->getWrappedResource(), $operation);
    }

    /**
     * @inheritDoc
     */
    public function streamMetadata(string $path, int $option, mixed $value): bool
    {
        return $this->unwrapped(function () use ($option, $path, $value) {
            switch ($option) {
                case STREAM_META_TOUCH:
                    /** @noinspection PotentialMalwareInspection */
                    return touch($path, $value[0] ?? null, $value[1] ?? null);
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
        StreamWrapperInterface $streamWrapper,
        string $path,
        string $mode,
        int $options,
        ?string &$openedPath
    ): ?array {
        $context = $streamWrapper->getContext();
        $usePath = (bool) ($options & STREAM_USE_PATH);

        $resource = $this->unwrapped(
            fn () => $context ?
                fopen($path, $mode, $usePath, $context) :
                fopen($path, $mode, $usePath)
        );

        if ($resource === false) {
            return null;
        }

        $including = $this->isInclude($options);

        if ($including) {
            // Perform any applicable shifts for the included PHP module file,
            // returning the new underlying resource to use for this open stream.
            $resource = $this->streamShifter->shift($path, $resource);
        }

        if ($usePath && $openedPath) {
            $openedPath = realpath($path);
        }

        return ['resource' => $resource, 'isInclude' => $including];
    }

    /**
     * @inheritDoc
     */
    public function streamRead(StreamWrapperInterface $streamWrapper, int $count): string|false
    {
        return fread($streamWrapper->getWrappedResource(), $count);
    }

    /**
     * @inheritDoc
     */
    public function streamSeek(StreamWrapperInterface $streamWrapper, int $offset, int $whence = SEEK_SET): bool
    {
        // fseek(...) returns 0 on success.
        return fseek($streamWrapper->getWrappedResource(), $offset, $whence) === 0;
    }

    /**
     * @inheritDoc
     */
    public function streamSetOption(StreamWrapperInterface $streamWrapper, int $option, int $arg1, int|null $arg2): bool
    {
        return $this->unwrapped(
            fn () => match ($option) {
                STREAM_OPTION_BLOCKING => stream_set_blocking($streamWrapper->getWrappedResource(), (bool)$arg1),
                STREAM_OPTION_READ_TIMEOUT => stream_set_timeout($streamWrapper->getWrappedResource(), $arg1, $arg2),
                STREAM_OPTION_WRITE_BUFFER => stream_set_write_buffer($streamWrapper->getWrappedResource(), $arg1) === 0,
                STREAM_OPTION_READ_BUFFER => stream_set_read_buffer($streamWrapper->getWrappedResource(), $arg1) === 0,
                default => false,
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function streamStat(StreamWrapperInterface $streamWrapper): array|false
    {
        return fstat($streamWrapper->getWrappedResource());
    }

    /**
     * @inheritDoc
     */
    public function streamTell(StreamWrapperInterface $streamWrapper): int|false
    {
        return ftell($streamWrapper->getWrappedResource());
    }

    /**
     * @inheritDoc
     */
    public function streamTruncate(StreamWrapperInterface $streamWrapper, int $newSize): bool
    {
        return ftruncate($streamWrapper->getWrappedResource(), $newSize);
    }

    /**
     * @inheritDoc
     */
    public function streamWrite(StreamWrapperInterface $streamWrapper, string $data): int|false
    {
        return fwrite($streamWrapper->getWrappedResource(), $data);
    }

    /**
     * @inheritDoc
     */
    public function unlink(StreamWrapperInterface $streamWrapper, string $path): bool
    {
        $context = $streamWrapper->getContext();

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
        $link = (bool)($flags & STREAM_URL_STAT_LINK);
        $quiet = (bool)($flags & STREAM_URL_STAT_QUIET);

        return $this->statResolver->stat($path, link: $link, quiet: $quiet) ?? false;
    }

    /**
     * @inheritDoc
     */
    public function unwrapped(callable $callback): mixed
    {
        return $this->unwrapper->unwrapped($callback);
    }
}
