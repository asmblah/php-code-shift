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

namespace Asmblah\PhpCodeShift;

use Asmblah\PhpCodeShift\Shifter\Parser\ParserFactory;
use Asmblah\PhpCodeShift\Shifter\Printer\DelegatingNewNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\ExistingNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\NewNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\NodeCollectionPrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\NodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\NodeType\EncapsedStringPartNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\NodeType\IdentifierNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\NodeType\NameNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\NodeType\StaticCallNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\NodeType\StringLiteralNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Printer\SingleNodePrinter;
use Asmblah\PhpCodeShift\Shifter\Resolver\NodeResolver;
use Asmblah\PhpCodeShift\Shifter\Shift\Shifter\ShiftSetShifter;
use Asmblah\PhpCodeShift\Shifter\Stream\Resolver\ShiftSetResolver;
use Asmblah\PhpCodeShift\Shifter\Stream\Shifter\StreamShifter;
use Asmblah\PhpCodeShift\Shifter\Stream\Shifter\StreamShifterInterface;
use Asmblah\PhpCodeShift\Shifter\Stream\StreamWrapperManager;
use Asmblah\PhpCodeShift\Util\CallStack;
use Asmblah\PhpCodeShift\Util\CallStackInterface;
use PhpParser\ParserFactory as LibraryParserFactory;

/**
 * Class Shared.
 *
 * Manages all services shared between instances of PHP Code Shift.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class Shared
{
    private static ?CallStackInterface $callStack;
    private static ?StreamShifterInterface $streamShifter;

    public static function init(): void
    {
        self::$callStack = new CallStack();

        $nodeResolver = new NodeResolver();
        $nodePrinter = new NodePrinter();
        $existingNodePrinter = new ExistingNodePrinter();

        $delegatingNewNodePrinter = new DelegatingNewNodePrinter();
        $delegatingNewNodePrinter->registerNodePrinter(new EncapsedStringPartNodePrinter());
        $delegatingNewNodePrinter->registerNodePrinter(new IdentifierNodePrinter());
        $delegatingNewNodePrinter->registerNodePrinter(new NameNodePrinter());
        $delegatingNewNodePrinter->registerNodePrinter(new StaticCallNodePrinter($nodePrinter));
        $delegatingNewNodePrinter->registerNodePrinter(new StringLiteralNodePrinter());

        $newNodePrinter = new NewNodePrinter($nodeResolver, $delegatingNewNodePrinter);
        $singleNodePrinter = new SingleNodePrinter($existingNodePrinter, $newNodePrinter);
        $nodeCollectionPrinter = new NodeCollectionPrinter($singleNodePrinter);

        $nodePrinter->setSingleNodePrinter($singleNodePrinter);
        $nodePrinter->setNodeCollectionPrinter($nodeCollectionPrinter);

        self::$streamShifter = new StreamShifter(
            new ShiftSetResolver(),
            new ShiftSetShifter(
                (new ParserFactory(new LibraryParserFactory()))->createParser(),
                $nodeResolver,
                $nodePrinter
            )
        );

        StreamWrapperManager::init();
    }

    /**
     * Fetches the CallStack service.
     */
    public static function getCallStack(): CallStackInterface
    {
        return self::$callStack;
    }

    /**
     * Fetches the StreamShifter service.
     */
    public static function getStreamShifter(): StreamShifterInterface
    {
        return self::$streamShifter;
    }

    /**
     * Installs a new CallStack.
     */
    public static function setCallStack(CallStackInterface $callStack): void
    {
        self::$callStack = $callStack;
    }

    /**
     * Installs a new StreamShifter.
     */
    public static function setStreamShifter(StreamShifterInterface $streamShifter): void
    {
        self::$streamShifter = $streamShifter;
    }
}
