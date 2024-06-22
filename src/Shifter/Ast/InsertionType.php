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

namespace Asmblah\PhpCodeShift\Shifter\Ast;

/**
 * Enum InsertionType.
 *
 * Provides constants for supported AST node insertion types.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
enum InsertionType: string
{
    case AFTER_NODE = 'afterNode';
    case BEFORE_NODE = 'beforeNode';
    case FIRST_CHILD = 'firstChild';
    case NONE = 'none';
}
