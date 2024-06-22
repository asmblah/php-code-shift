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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter\Shift\Shifter;

use Asmblah\PhpCodeShift\Exception\ParseFailedException;
use Asmblah\PhpCodeShift\Shifter\Parser\ParserFactory;
use Asmblah\PhpCodeShift\Shifter\Printer\NodePrinterInterface;
use Asmblah\PhpCodeShift\Shifter\Printer\PrintedNode;
use Asmblah\PhpCodeShift\Shifter\Resolver\CodeModificationExtents;
use Asmblah\PhpCodeShift\Shifter\Resolver\ExtentResolverInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Ast\ModificationInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Code\Context\ModificationContextInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Shifter\ShiftSetShifter;
use Asmblah\PhpCodeShift\Shifter\Shift\ShiftInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\ShiftSetInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Traverser\Ast\AstModificationTraverserInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Traverser\Visitor\NodeVisitorInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery\MockInterface;
use PhpParser\Node;
use PhpParser\Parser;
use PhpParser\ParserFactory as LibraryParserFactory;

/**
 * Class ShiftSetShifterTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ShiftSetShifterTest extends AbstractTestCase
{
    private MockInterface&ExtentResolverInterface $extentResolver;
    private MockInterface&NodePrinterInterface $nodePrinter;
    private MockInterface&Parser $parser;
    private Parser $realParser;
    private MockInterface&ShiftInterface $shift;
    private ShiftSetShifter $shifter;
    private MockInterface&ShiftSetInterface $shiftSet;

    public function setUp(): void
    {
        $this->extentResolver = mock(ExtentResolverInterface::class, [
            'resolveModificationExtents' => null,
        ]);
        $this->nodePrinter = mock(NodePrinterInterface::class);
        $this->parser = mock(Parser::class);
        $this->realParser = (new ParserFactory(new LibraryParserFactory()))->createParser();
        $this->shift = mock(ShiftInterface::class, [
            'configureTraversal' => null,
        ]);
        $this->shiftSet = mock(ShiftSetInterface::class, [
            'getPath' => '/path/to/my_module.php',
            'getShifts' => [$this->shift],
        ]);

        $this->parser->allows('parse')
            ->andReturnUsing(function (string $code) {
                return $this->realParser->parse($code);
            })
            ->byDefault();

        $this->shifter = new ShiftSetShifter(
            $this->parser,
            $this->extentResolver,
            $this->nodePrinter
        );
    }

    public function testShiftReturnsTheShiftedContents(): void
    {
        $replacementNode = null;
        $this->shift->allows('configureTraversal')
            ->andReturnUsing(function (AstModificationTraverserInterface $traverser) use (&$replacementNode) {
                $nodeVisitor = mock(NodeVisitorInterface::class);
                $nodeVisitor->allows('enterNode')
                    ->andReturnUsing(function (Node $node) use (&$replacementNode) {
                        if ($node instanceof Node\Name && $node->toCodeString() === '\myOriginalContents') {
                            $replacementNode = new Node\Name('myShiftedContents', $node->getAttributes());

                            return mock(ModificationInterface::class, [
                                'getLibraryResult' => $replacementNode,
                            ]);
                        }

                        return null;
                    })
                    ->byDefault();

                $traverser->addVisitor($nodeVisitor);
            });
        $this->extentResolver->allows('resolveModificationExtents')
            ->andReturnUsing(function (Node $node, ModificationContextInterface $modificationContext) use (&$replacementNode) {
                if ($node === $replacementNode) {
                    return new CodeModificationExtents(
                        startOffset: 6,
                        startLine: 1,
                        endOffset: 24,
                        endLine: 1
                    );
                }

                return null;
            });
        $this->nodePrinter->allows('printNode')
            ->andReturnUsing(function (Node $node) use (&$replacementNode) {
                if ($node === $replacementNode) {
                    return new PrintedNode('myShiftedContents', 1, 1);
                }

                $this->fail('Unexpected node print');
            });

        static::assertSame(
            '<?php myShiftedContents();',
            $this->shifter->shift('<?php myOriginalContents();', $this->shiftSet)
        );
    }

    public function testShiftRaisesParseFailedExceptionOnParseFailure(): void
    {
        $this->expectException(ParseFailedException::class);
        $this->expectExceptionMessage(
            'Failed to parse path "/path/to/my_module.php" :: PhpParser\Error "Syntax error, unexpected T_STRING on line 1"'
        );

        $this->shifter->shift('<?php I am a syntax error;', $this->shiftSet);
    }
}
