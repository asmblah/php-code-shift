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

namespace Asmblah\PhpCodeShift\Shifter\Ast;

/**
 * Interface NodeAttribute.
 *
 * Provides constants for supported AST node attributes.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface NodeAttribute
{
    public const END_FILE_POS = 'endFilePos';
    public const END_LINE = 'endLine';
    public const REPLACED_NODE = 'replacedNode';
    public const REPLACEMENT_NODE = 'replacementNode';
    public const START_FILE_POS = 'startFilePos';
    public const START_LINE = 'startLine';
}
