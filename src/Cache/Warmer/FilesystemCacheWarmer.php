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

namespace Asmblah\PhpCodeShift\Cache\Warmer;

use Asmblah\PhpCodeShift\Cache\Adapter\FilesystemCacheAdapterInterface;
use Asmblah\PhpCodeShift\Exception\FileNotCachedException;
use Asmblah\PhpCodeShift\Exception\ParseFailedException;
use Asmblah\PhpCodeShift\Filesystem\FilesystemInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Shifter\ShiftSetShifterInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\Resolver\ShiftSetResolverInterface;
use Psr\Log\LoggerInterface;

/**
 * Class FilesystemCacheWarmer.
 *
 * Shifts and warms files into the filesystem cache.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class FilesystemCacheWarmer implements WarmerInterface
{
    public function __construct(
        private readonly FilesystemCacheAdapterInterface $cacheAdapter,
        private readonly FilesystemInterface $filesystem,
        private readonly ShiftSetResolverInterface $shiftSetResolver,
        private readonly ShiftSetShifterInterface $shiftSetShifter,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritDoc
     */
    public function warmFile(string $filePath): void
    {
        $shiftSet = $this->shiftSetResolver->resolveShiftSet($filePath);

        if ($shiftSet === null) {
            // No shifts apply to this file - nothing to do.
            return;
        }

        $originalContents = $this->filesystem->readFile($filePath);

        try {
            $shiftedContents = $this->shiftSetShifter->shift($originalContents, $shiftSet);
        } catch (ParseFailedException $exception) {
            // Log but continue warming other files.
            $this->logger->warning('Nytris Shift failed to shift file', [
                'path' => $filePath,
                'exception' => [
                    'class' => $exception->getPrevious()::class,
                    'message' => $exception->getMessage(),
                ]
            ]);

            return;
        }

        try {
            $this->cacheAdapter->saveFile($filePath, $shiftedContents);
        } catch (FileNotCachedException $exception) {
            // Log but continue warming other files.
            $this->logger->error('Nytris Shift failed to save cache file', [
                'path' => $filePath,
                'exception' => [
                    'message' => $exception->getMessage(),
                ]
            ]);

            return;
        }

        $this->logger->info('Nytris Shift successfully warmed cache file', [
            'path' => $filePath,
        ]);
    }
}
