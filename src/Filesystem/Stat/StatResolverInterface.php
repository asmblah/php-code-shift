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

namespace Asmblah\PhpCodeShift\Filesystem\Stat;

/**
 * Interface StatResolverInterface.
 *
 * Abstraction over resolving file or directory statuses.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface StatResolverInterface
{
    /**
     * Fetches the status of the given path.
     *
     * @return array<mixed>|null
     */
    public function stat(string $path, bool $link, bool $quiet): array|null;
}
