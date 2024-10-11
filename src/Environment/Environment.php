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
 * Class Environment.
 *
 * Abstraction over the execution environment.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Environment implements EnvironmentInterface
{
    private string $cwd;

    public function __construct()
    {
        $this->cwd = getcwd();
    }

    /**
     * @inheritDoc
     */
    public function invalidate(): void
    {
        $this->cwd = getcwd();
    }

    /**
     * @inheritDoc
     */
    public function getCwd(): string
    {
        return $this->cwd;
    }
}
