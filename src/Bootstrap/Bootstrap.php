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

namespace Asmblah\PhpCodeShift\Bootstrap;

use Asmblah\PhpCodeShift\Cache\Cache;
use Asmblah\PhpCodeShift\Cache\CacheInterface;
use Asmblah\PhpCodeShift\Cache\Provider\CacheProviderInterface;
use Asmblah\PhpCodeShift\Shifter\Parser\ParserFactory;
use Asmblah\PhpCodeShift\Shifter\Printer\DelegatingNewNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\ExistingNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\NewNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\NodeCollectionPrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\NodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\NodeType\EncapsedStringPartNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\NodeType\ExpressionStatementNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\NodeType\IdentifierNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\NodeType\NameNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\NodeType\StaticCallNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\NodeType\StringLiteralNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\SingleNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Resolver\ExtentResolver;
use Asmblah\PhpCodeShift\Shifter\Resolver\NodeResolver;
use Asmblah\PhpCodeShift\Shifter\Shift\Shifter\ShiftSetShifter;
use Asmblah\PhpCodeShift\Shifter\Stream\Resolver\ShiftSetResolver;
use Asmblah\PhpCodeShift\Shifter\Stream\Shifter\StreamShifter;
use Asmblah\PhpCodeShift\Shifter\Stream\Shifter\StreamShifterInterface;
use LogicException;
use PhpParser\ParserFactory as LibraryParserFactory;

/**
 * Class Bootstrap.
 *
 * Bootstraps PHP Code Shift.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Bootstrap implements BootstrapInterface
{
    private ?CacheInterface $cache = null;
    private bool $installed = false;
    private ?StreamShifterInterface $streamShifter = null;

    /**
     * @inheritDoc
     */
    public function getCache(): CacheInterface
    {
        if ($this->cache === null) {
            throw new LogicException('Cannot fetch Cache - not installed');
        }

        return $this->cache;
    }

    /**
     * @inheritDoc
     */
    public function getStreamShifter(): StreamShifterInterface
    {
        if ($this->streamShifter === null) {
            throw new LogicException('Cannot fetch StreamShifter - not installed');
        }

        return $this->streamShifter;
    }

    /**
     * @inheritDoc
     */
    public function install(CacheProviderInterface $cacheProvider): void
    {
        if ($this->installed) {
            throw new LogicException('PHP Code Shift already installed');
        }

        $nodeResolver = new NodeResolver();
        $nodePrinter = new NodePrinter();
        $existingNodePrinter = new ExistingNodePrinter();

        $delegatingNewNodePrinter = new DelegatingNewNodePrinter();
        $delegatingNewNodePrinter->registerNodePrinter(new EncapsedStringPartNodePrinter());
        $delegatingNewNodePrinter->registerNodePrinter(new ExpressionStatementNodePrinter($nodePrinter));
        $delegatingNewNodePrinter->registerNodePrinter(new IdentifierNodePrinter());
        $delegatingNewNodePrinter->registerNodePrinter(new NameNodePrinter());
        $delegatingNewNodePrinter->registerNodePrinter(new StaticCallNodePrinter($nodePrinter));
        $delegatingNewNodePrinter->registerNodePrinter(new StringLiteralNodePrinter());

        $extentResolver = new ExtentResolver($nodeResolver);

        $newNodePrinter = new NewNodePrinter($extentResolver, $delegatingNewNodePrinter);
        $singleNodePrinter = new SingleNodePrinter($existingNodePrinter, $newNodePrinter);
        $nodeCollectionPrinter = new NodeCollectionPrinter($singleNodePrinter);

        $nodePrinter->setSingleNodePrinter($singleNodePrinter);
        $nodePrinter->setNodeCollectionPrinter($nodeCollectionPrinter);

        $cacheAdapter = $cacheProvider->createCacheAdapter();

        $shiftSetResolver = new ShiftSetResolver();
        $shiftSetShifter = new ShiftSetShifter(
            (new ParserFactory(new LibraryParserFactory()))->createParser(),
            $extentResolver,
            $nodePrinter
        );
        $this->streamShifter = new StreamShifter(
            $shiftSetResolver,
            $shiftSetShifter,
            $cacheAdapter
        );

        $cacheDriver = $cacheProvider->createCacheDriver(
            $cacheAdapter,
            $shiftSetResolver,
            $shiftSetShifter
        );
        $this->cache = new Cache($cacheDriver);

        $this->installed = true;
    }

    /**
     * @inheritDoc
     */
    public function isInstalled(): bool
    {
        return $this->installed;
    }

    /**
     * @inheritDoc
     */
    public function uninstall(): void
    {
        $this->installed = false;
    }
}
