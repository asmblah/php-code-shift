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

use Asmblah\PhpCodeShift\Environment\EnvironmentInterface;

/**
 * Class Canonicaliser.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Canonicaliser implements CanonicaliserInterface
{
    public function __construct(
        private readonly EnvironmentInterface $environment
    ) {
    }

    /**
     * @inheritDoc
     */
    public function canonicalise(string $path, ?string $cwd = null): string
    {
        $cwd ??= $this->environment->getCwd();

        // Resolve same- or parent directory prefix segment.
        if (str_starts_with($path, './') || str_starts_with($path, '../')) {
            $path = $cwd . '/' . $path;
        }

        // Resolve redundant dot same-directory symbols "a/./b".
        $path = preg_replace('#(?:^|/)(?:\./)+#', '/', $path);

        // Resolve redundant slash same-directory symbols "a//b".
        $path = preg_replace('#/{2,}#', '/', $path);

        // Resolve parent directory symbols "a/../b".
        $count = 0;

        do {
            $path = preg_replace('#(?:^|/|\G)(?!\.\./)([^/]+)/\.\./#', '/', $path, count: $count);
        } while ($count > 0);

        return $path;
    }
}
