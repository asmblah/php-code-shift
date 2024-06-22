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

namespace Asmblah\PhpCodeShift\Exception;

use Exception;
use PhpParser\Error;

/**
 * Class ParseFailedException.
 *
 * Represents that parsing of code for a module failed.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ParseFailedException extends Exception implements ExceptionInterface
{
    public function __construct(
        private readonly string $path,
        Error $exception
    ) {
        parent::__construct(
            sprintf(
                'Failed to parse path "%s" :: %s "%s"',
                $path,
                $exception::class,
                $exception->getMessage()
            ),
            $exception->getCode(),
            $exception
        );
    }

    /**
     * Fetches the path to the file with the syntax error.
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
