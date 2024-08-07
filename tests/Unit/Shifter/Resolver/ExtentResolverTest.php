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

namespace Asmblah\PhpCodeShift\Tests\Unit\Shifter\Resolver;

use Asmblah\PhpCodeShift\Shifter\Ast\InsertionType;
use Asmblah\PhpCodeShift\Shifter\Ast\NodeAttribute;
use Asmblah\PhpCodeShift\Shifter\Resolver\ExtentResolver;
use Asmblah\PhpCodeShift\Shifter\Resolver\NodeResolverInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Modification\Code\Context\ModificationContextInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use LogicException;
use Mockery\MockInterface;
use PhpParser\Node;
use stdClass;

/**
 * Class ExtentResolverTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class ExtentResolverTest extends AbstractTestCase
{
    private ExtentResolver $extentResolver;
    private MockInterface&ModificationContextInterface $modificationContext;
    private MockInterface&Node $node;
    private MockInterface&NodeResolverInterface $nodeResolver;

    public function setUp(): void
    {
        $this->modificationContext = mock(ModificationContextInterface::class);
        $this->node = mock(Node::class);
        $this->nodeResolver = mock(NodeResolverInterface::class, [
            'extractReplacedNode' => null,
        ]);

        $this->node->allows()
            ->getAttribute(NodeAttribute::TRAVERSE_INSIDE, false)
            ->andReturn(false)
            ->byDefault();

        $this->extentResolver = new ExtentResolver($this->nodeResolver);
    }

    public function testResolveModificationExtentsReturnsNullWhenNodeHasTraverseInsideAttributeSet(): void
    {
        $this->node->allows()
            ->getAttribute(NodeAttribute::TRAVERSE_INSIDE, false)
            ->andReturn(true);

        static::assertNull($this->extentResolver->resolveModificationExtents($this->node, $this->modificationContext));
    }

    public function testResolveModificationExtentsReturnsExtentsMatchingAnExistingAstNode(): void
    {
        $replacedNode = mock(Node::class, [
            'getStartFilePos' => 21,
            'getStartLine' => 7,
            'getEndFilePos' => 101,
            'getEndLine' => 9,
        ]);
        $this->nodeResolver->allows()
            ->extractReplacedNode($this->node)
            ->andReturn($replacedNode);

        $extents = $this->extentResolver->resolveModificationExtents($this->node, $this->modificationContext);

        static::assertSame(21, $extents->getStartOffset());
        static::assertSame(7, $extents->getStartLine());
        static::assertSame(101 + 1, $extents->getEndOffset(), 'End offset should be incremented by 1');
        static::assertSame(9, $extents->getEndLine());
    }

    public function testResolveModificationExtentsReturnsExtentsJustBeforeUnreplacedSiblingForBeforeNodeInsertionType(): void
    {
        $nextSibling = mock(Node::class, [
            'getStartFilePos' => 200,
            'getStartLine' => 7,
            'getEndFilePos' => 220,
            'getEndLine' => 9,
        ]);
        $this->node->allows()
            ->getAttribute(NodeAttribute::INSERTION_TYPE, InsertionType::NONE)
            ->andReturn(InsertionType::BEFORE_NODE);
        $this->node->allows()
            ->getAttribute(NodeAttribute::NEXT_SIBLING)
            ->andReturn($nextSibling);

        $extents = $this->extentResolver->resolveModificationExtents($this->node, $this->modificationContext);

        // Extents should be at the location just before the sibling.
        static::assertSame(200, $extents->getStartOffset());
        static::assertSame(7, $extents->getStartLine());
        static::assertSame(200, $extents->getEndOffset());
        static::assertSame(7, $extents->getEndLine());
    }

    public function testResolveModificationExtentsReturnsExtentsJustBeforeReplacedSiblingForBeforeNodeInsertionType(): void
    {
        $nextSibling = mock(Node::class);
        $replacedNextSibling = mock(Node::class, [
            'getStartFilePos' => 300,
            'getStartLine' => 7,
            'getEndFilePos' => 320,
            'getEndLine' => 9,
        ]);
        $this->node->allows()
            ->getAttribute(NodeAttribute::INSERTION_TYPE, InsertionType::NONE)
            ->andReturn(InsertionType::BEFORE_NODE);
        $this->node->allows()
            ->getAttribute(NodeAttribute::NEXT_SIBLING)
            ->andReturn($nextSibling);
        $this->nodeResolver->allows()
            ->extractReplacedNode($nextSibling)
            ->andReturn($replacedNextSibling);

        $extents = $this->extentResolver->resolveModificationExtents($this->node, $this->modificationContext);

        // Extents should be at the location just before the original/replaced sibling.
        static::assertSame(300, $extents->getStartOffset());
        static::assertSame(7, $extents->getStartLine());
        static::assertSame(300, $extents->getEndOffset());
        static::assertSame(7, $extents->getEndLine());
    }

    public function testResolveModificationExtentsRaisesExceptionForBeforeNodeInsertionTypeWhenMissingNextSiblingAttribute(): void
    {
        $this->node->allows()
            ->getAttribute(NodeAttribute::INSERTION_TYPE, InsertionType::NONE)
            ->andReturn(InsertionType::BEFORE_NODE);
        $this->node->allows()
            ->getAttribute(NodeAttribute::NEXT_SIBLING)
            ->andReturnNull();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Missing attribute ::NEXT_SIBLING for insertion type ::BEFORE_NODE');

        $this->extentResolver->resolveModificationExtents($this->node, $this->modificationContext);
    }

    public function testResolveModificationExtentsReturnsExtentsJustBeforeUnreplacedSiblingForFirstChildInsertionType(): void
    {
        $parentNode = mock(Node::class);
        $nextSibling = mock(Node::class, [
            'getStartFilePos' => 200,
            'getStartLine' => 7,
            'getEndFilePos' => 220,
            'getEndLine' => 9,
        ]);
        $parentNode->stmts = [$this->node, $nextSibling];
        $this->node->allows()
            ->getAttribute(NodeAttribute::INSERTION_TYPE, InsertionType::NONE)
            ->andReturn(InsertionType::FIRST_CHILD);
        $this->node->allows()
            ->getAttribute(NodeAttribute::PARENT_NODE)
            ->andReturn($parentNode);

        $extents = $this->extentResolver->resolveModificationExtents($this->node, $this->modificationContext);

        // Extents should be at the location just before the sibling.
        static::assertSame(200, $extents->getStartOffset());
        static::assertSame(7, $extents->getStartLine());
        static::assertSame(200, $extents->getEndOffset());
        static::assertSame(7, $extents->getEndLine());
    }

    public function testResolveModificationExtentsReturnsExtentsJustBeforeReplacedSiblingForFirstChildInsertionType(): void
    {
        $parentNode = mock(Node::class);
        $nextSibling = mock(Node::class);
        $replacedNextSibling = mock(Node::class, [
            'getStartFilePos' => 300,
            'getStartLine' => 7,
            'getEndFilePos' => 320,
            'getEndLine' => 9,
        ]);
        $parentNode->stmts = [$this->node, $replacedNextSibling];
        $this->node->allows()
            ->getAttribute(NodeAttribute::INSERTION_TYPE, InsertionType::NONE)
            ->andReturn(InsertionType::FIRST_CHILD);
        $this->node->allows()
            ->getAttribute(NodeAttribute::PARENT_NODE)
            ->andReturn($parentNode);
        $this->nodeResolver->allows()
            ->extractReplacedNode($nextSibling)
            ->andReturn($replacedNextSibling);

        $extents = $this->extentResolver->resolveModificationExtents($this->node, $this->modificationContext);

        // Extents should be at the location just before the original/replaced sibling.
        static::assertSame(300, $extents->getStartOffset());
        static::assertSame(7, $extents->getStartLine());
        static::assertSame(300, $extents->getEndOffset());
        static::assertSame(7, $extents->getEndLine());
    }

    public function testResolveModificationExtentsReturnsExtentsJustInsideEmptyParentForFirstChildInsertionType(): void
    {
        $this->modificationContext->allows()
            ->getContents()
            ->andReturn('<?php if ($a) { if ($b) { if ($c) {} } }');
        $this->modificationContext->allows()
            ->getDelta()
            ->andReturn(10);
        $parentNode = mock(Node::class, [
            'getEndFilePos' => 38 - 10, // Delta of 10 will be applied.
            'getEndLine' => 8,
        ]);
        $parentNode->stmts = [$this->node];
        $this->node->allows()
            ->getAttribute(NodeAttribute::INSERTION_TYPE, InsertionType::NONE)
            ->andReturn(InsertionType::FIRST_CHILD);
        $this->node->allows()
            ->getAttribute(NodeAttribute::PARENT_NODE)
            ->andReturn($parentNode);

        $extents = $this->extentResolver->resolveModificationExtents($this->node, $this->modificationContext);

        // Extents should be at the location just inside the parent node's brace-delimited block.
        // Note delta of 10 has been un-applied.
        static::assertSame(37 - 10, $extents->getStartOffset());
        static::assertSame(8, $extents->getStartLine());
        static::assertSame(37 - 10, $extents->getEndOffset());
        static::assertSame(8, $extents->getEndLine());
    }

    public function testResolveModificationExtentsRaisesExceptionWhenEmptyParentIsMissingBoundsForFirstChildInsertionType(): void
    {
        $parentNode = mock(Node::class, [
            'getEndFilePos' => -1,
        ]);
        $nextSibling = mock(Node::class);
        $replacedNextSibling = mock(Node::class, [
            'getStartFilePos' => 300,
            'getStartLine' => 7,
            'getEndFilePos' => 320,
            'getEndLine' => 9,
        ]);
        $parentNode->stmts = [$this->node];
        $this->node->allows()
            ->getAttribute(NodeAttribute::INSERTION_TYPE, InsertionType::NONE)
            ->andReturn(InsertionType::FIRST_CHILD);
        $this->node->allows()
            ->getAttribute(NodeAttribute::PARENT_NODE)
            ->andReturn($parentNode);
        $this->nodeResolver->allows()
            ->extractReplacedNode($nextSibling)
            ->andReturn($replacedNextSibling);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Parent node is missing end file position');

        $this->extentResolver->resolveModificationExtents($this->node, $this->modificationContext);
    }

    public function testResolveModificationExtentsRaisesExceptionWhenEmptyParentClosingBraceCannotBeFoundForFirstChildInsertionType(): void
    {
        $this->modificationContext->allows()
            ->getContents()
            ->andReturn('<?php if ($a) { if ($b) { if ($c) {} } }');
        $this->modificationContext->allows()
            ->getDelta()
            ->andReturn(10);
        $parentNode = mock(Node::class, [
            'getEndFilePos' => 12 - 10, // Delta of 10 will be applied.
            'getEndLine' => 8,
        ]);
        $parentNode->stmts = [$this->node];
        $this->node->allows()
            ->getAttribute(NodeAttribute::INSERTION_TYPE, InsertionType::NONE)
            ->andReturn(InsertionType::FIRST_CHILD);
        $this->node->allows()
            ->getAttribute(NodeAttribute::PARENT_NODE)
            ->andReturn($parentNode);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot find closing brace of parent node');

        $this->extentResolver->resolveModificationExtents($this->node, $this->modificationContext);
    }

    public function testResolveModificationExtentsRaisesExceptionForFirstChildInsertionTypeWhenMissingNextSiblingAttribute(): void
    {
        $this->node->allows()
            ->getAttribute(NodeAttribute::INSERTION_TYPE, InsertionType::NONE)
            ->andReturn(InsertionType::FIRST_CHILD);
        $this->node->allows()
            ->getAttribute(NodeAttribute::PARENT_NODE)
            ->andReturnNull();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Missing attribute ::PARENT_NODE for insertion type ::FIRST_CHILD');

        $this->extentResolver->resolveModificationExtents($this->node, $this->modificationContext);
    }

    public function testResolveModificationExtentsRaisesExceptionForFirstChildInsertionTypeWhenParentNodeInvalid(): void
    {
        $this->node->allows()
            ->getAttribute(NodeAttribute::INSERTION_TYPE, InsertionType::NONE)
            ->andReturn(InsertionType::FIRST_CHILD);
        $this->node->allows()
            ->getAttribute(NodeAttribute::PARENT_NODE)
            ->andReturn(new stdClass()); // Not a valid AST node.

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Parent node is not a valid AST node');

        $this->extentResolver->resolveModificationExtents($this->node, $this->modificationContext);
    }

    public function testResolveModificationExtentsRaisesExceptionForFirstChildInsertionTypeWhenParentNodeDoesNotHaveChildStatementsProperty(): void
    {
        $parentNode = mock(Node::class); // No ->stmts property defined.
        $this->node->allows()
            ->getAttribute(NodeAttribute::INSERTION_TYPE, InsertionType::NONE)
            ->andReturn(InsertionType::FIRST_CHILD);
        $this->node->allows()
            ->getAttribute(NodeAttribute::PARENT_NODE)
            ->andReturn($parentNode);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Parent node does not have child ->stmts');

        $this->extentResolver->resolveModificationExtents($this->node, $this->modificationContext);
    }

    public function testResolveModificationExtentsReturnsNullForNoneInsertionType(): void
    {
        $this->node->allows()
            ->getAttribute(NodeAttribute::INSERTION_TYPE, InsertionType::NONE)
            ->andReturn(InsertionType::NONE);
        $this->nodeResolver->allows()
            ->extractReplacedNode($this->node)
            ->andReturnNull();

        static::assertNull($this->extentResolver->resolveModificationExtents($this->node, $this->modificationContext));
    }

    public function testResolveModificationExtentsRaisesExceptionWhenInvalidInsertionType(): void
    {
        $this->node->allows()
            ->getAttribute(NodeAttribute::INSERTION_TYPE, InsertionType::NONE)
            ->andReturn('some-invalid-type');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Unknown insertion type "some-invalid-type"');

        $this->extentResolver->resolveModificationExtents($this->node, $this->modificationContext);
    }
}
