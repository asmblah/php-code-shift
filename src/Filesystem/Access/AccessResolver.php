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

namespace Asmblah\PhpCodeShift\Filesystem\Access;

use Asmblah\PhpCodeShift\Shifter\Stream\Unwrapper\UnwrapperInterface;

/**
 * Class AccessResolver.
 *
 * Abstraction over resolving file or directory access.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class AccessResolver implements AccessResolverInterface
{
    public function __construct(
        private readonly UnwrapperInterface $unwrapper
    ) {
    }

    /**
     * @inheritDoc
     */
    public function isExecutable(string $path): bool
    {
        return $this->unwrapper->unwrapped(static fn () => is_executable($path));
    }

    /**
     * @inheritDoc
     */
    public function isReadable(string $path): bool
    {
        return $this->unwrapper->unwrapped(static fn () => is_readable($path));
    }

    /**
     * @inheritDoc
     */
    public function isWritable(string $path): bool
    {
        return $this->unwrapper->unwrapped(static fn () => is_writable($path));
    }
}
