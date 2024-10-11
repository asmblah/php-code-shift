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

namespace Asmblah\PhpCodeShift\Filesystem;

use Asmblah\PhpCodeShift\Exception\NativeFileOperationFailedException;
use Closure;
use Exception;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use RuntimeException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

/**
 * Class Filesystem.
 *
 * Abstraction over the filesystem.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Filesystem implements FilesystemInterface
{
    private readonly Closure $filePutContents;

    public function __construct(
        private readonly SymfonyFilesystem $symfonyFilesystem,
        ?Closure $filePutContents = null
    ) {
        $this->filePutContents = $filePutContents ?? file_put_contents(...);
    }

    /**
     * @inheritDoc
     */
    public function directoryExists(string $path): bool
    {
        return is_dir($path);
    }

    /**
     * @inheritDoc
     */
    public function fileExists(string $path): bool
    {
        return is_file($path);
    }

    /**
     * @inheritDoc
     */
    public function glob(string $pattern): array
    {
        return glob($pattern, GLOB_BRACE);
    }

    /**
     * @inheritDoc
     */
    public function iterateDirectory(string $path, string $pattern): RegexIterator
    {
        $directoryIterator = new RecursiveDirectoryIterator(
            $path,
            FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_PATHNAME
        );

        return new RegexIterator(
            new RecursiveIteratorIterator($directoryIterator),
            $pattern,
            RegexIterator::GET_MATCH
        );
    }

    /**
     * @inheritDoc
     */
    public function mkdir(string $path): void
    {
        $this->symfonyFilesystem->mkdir($path);
    }

    /**
     * @inheritDoc
     */
    public function openForRead(string $path)
    {
        return fopen($path, 'rb');
    }

    /**
     * @inheritDoc
     */
    public function readFile(string $path): string
    {
        return file_get_contents($path);
    }

    /**
     * @inheritDoc
     */
    public function remove(string $path): void
    {
        $this->symfonyFilesystem->remove($path);
    }

    /**
     * @inheritDoc
     */
    public function writeFile(string $path, string $contents): void
    {
        try {
            $this->symfonyFilesystem->mkdir(dirname($path));

            // Symfony Filesystem ->dumpFile(...) does not always set permissions correctly when ACLs are at play.
            if (($this->filePutContents)($path, $contents) === false) {
                $this->raiseIoWriteFailure(
                    $path,
                    strlen($contents),
                    new RuntimeException('Failed to write file contents')
                );
            }
        } catch (IOException $exception) {
            $this->raiseIoWriteFailure($path, strlen($contents), $exception);
        }
    }

    /**
     * Raises an exception following a failure during file write.
     *
     * @param string $path
     * @param int $contentsLength
     * @param Exception $exception
     * @throws NativeFileOperationFailedException
     */
    private function raiseIoWriteFailure(string $path, int $contentsLength, Exception $exception): void
    {
        throw new NativeFileOperationFailedException(
            sprintf(
                'Failed to write %d byte(s) to file path: "%s"',
                $contentsLength,
                $path
            ),
            0,
            $exception
        );
    }
}
