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

namespace Asmblah\PhpCodeShift\Cache;

use Asmblah\PhpCodeShift\Exception\FileNotCachedException;

/**
 * Class MemoryCacheAdapter.
 *
 * Manages the storage of shifted code in memory.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class MemoryCacheAdapter implements CacheAdapterInterface
{
    /**
     * @var array<string, resource>
     */
    private array $pathToResource = [];

    /**
     * @inheritDoc
     */
    public function hasFile(string $path): bool
    {
        return array_key_exists($path, $this->pathToResource);
    }

    /**
     * @inheritDoc
     */
    public function openFile(string $path)
    {
        if (!$this->hasFile($path)) {
            throw new FileNotCachedException('Path not cached: ' . $path);
        }

        return $this->pathToResource[$path];
    }

    /**
     * @inheritDoc
     */
    public function saveFile(string $path, string $shiftedContents): void
    {
        $resource = fopen('php://memory', 'wb+');

        fwrite($resource, $shiftedContents);
        rewind($resource);

        $this->pathToResource[$path] = $resource;
    }
}
