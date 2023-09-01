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

namespace Asmblah\PhpCodeShift\Shifter\Parser;

use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\ParserFactory as LibraryParserFactory;

/**
 * Class ParserFactory.
 *
 * Default implementation that creates an appropriate PHP parser instance.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ParserFactory implements ParserFactoryInterface
{
    public function __construct(
        private readonly LibraryParserFactory $libraryParserFactory
    ) {
    }

    /**
     * @inheritDoc
     */
    public function createParser(): Parser
    {
        return $this->libraryParserFactory->create(
            LibraryParserFactory::PREFER_PHP7,
            new Lexer([
                'usedAttributes' => [
                    'comments',
                    'startLine',
                    'endLine',

                    // For code modifications to use.
                    'startFilePos',
                    'endFilePos'
                ],
            ])
        );
    }
}
