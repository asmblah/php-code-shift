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

namespace Asmblah\PhpCodeShift\Shifter\Modifier;

/**
 * Interface ModificationInterface.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface ModificationInterface
{
    /**
     * Performs this modification, updating the context as appropriate
     * and returning the modified contents.
     */
    public function perform(string $contents, ContextInterface $context): string;
}
