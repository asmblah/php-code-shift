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

namespace Asmblah\PhpCodeShift\Filesystem\Stat;

use Asmblah\PhpCodeShift\Shifter\Stream\Unwrapper\UnwrapperInterface;
use RuntimeException;

/**
 * Class NativeStatResolver.
 *
 * Resolves filesystem statuses using the native stream wrapper.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class NativeStatResolver implements StatResolverInterface
{
    public function __construct(
        private readonly UnwrapperInterface $unwrapper
    ) {
    }

    /**
     * @inheritDoc
     */
    public function stat(string $path, bool $link, bool $quiet): array|null
    {
        // Use lstat(...) for links but stat() for other files.
        $doStat = static function () use ($link, $path) {
            try {
                return $link ?
                    lstat($path) :
                    stat($path);
            } catch (RuntimeException) {
                /*
                 * Stream wrapper must have been invoked by SplFileInfo::__construct(),
                 * which raises RuntimeExceptions in place of warnings
                 * such as `RuntimeException: stat(): stat failed for .../non_existent.txt`.
                 */
                return false;
            }
        };

        // Suppress warnings/notices if quiet flag is set.
        $stat = $this->unwrapper->unwrapped(
            $quiet ?
                static fn () => @$doStat() :
                $doStat
        );

        return $stat !== false ? $stat : null;
    }
}
