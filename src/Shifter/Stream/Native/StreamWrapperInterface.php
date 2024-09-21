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

namespace Asmblah\PhpCodeShift\Shifter\Stream\Native;

use Asmblah\PhpCodeShift\Exception\NoWrappedResourceAvailableException;

/**
 * Interface StreamWrapperInterface.
 *
 * Provides access to data about the current stream. A stream wrapper instance
 * will already be allocated by the stream wrapper mechanism - reusing that instance
 * by having it implement this interface saves on heap and GC pressure.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
interface StreamWrapperInterface
{
    /**
     * Fetches the current context, or null if none.
     *
     * @return resource|null
     */
    public function getContext();

    /**
     * Fetches the mode that the stream is currently open in.
     *
     * @throws NoWrappedResourceAvailableException
     */
    public function getOpenMode(): string;

    /**
     * Fetches the path that is currently open.
     *
     * @throws NoWrappedResourceAvailableException
     */
    public function getOpenPath(): string;

    /**
     * Fetches the wrapped resource.
     *
     * @return resource
     *
     * @throws NoWrappedResourceAvailableException
     */
    public function getWrappedResource();

    /**
     * Fetches whether this stream access is for a PHP include.
     */
    public function isInclude(): bool;
}
