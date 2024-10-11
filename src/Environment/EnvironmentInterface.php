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

namespace Asmblah\PhpCodeShift\Environment;

/**
 * Interface EnvironmentInterface.
 *
 * Abstraction over the execution environment.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface EnvironmentInterface
{
    /**
     * Fetches the (cached) current working directory.
     */
    public function getCwd(): string;

    /**
     * Re-fetches the current working directory.
     */
    public function invalidate(): void;
}
