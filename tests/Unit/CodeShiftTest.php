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

namespace Asmblah\PhpCodeShift\Tests\Unit;

use Asmblah\PhpCodeShift\CodeShift;
use Asmblah\PhpCodeShift\Shifter\Filter\DenyListInterface;
use Asmblah\PhpCodeShift\Shifter\Filter\FileFilter;
use Asmblah\PhpCodeShift\Shifter\Filter\FileFilterInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\DelegatingShiftInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\ShiftTypeInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Spec\ShiftSpecInterface;
use Asmblah\PhpCodeShift\Shifter\ShifterInterface;
use Asmblah\PhpCodeShift\Tests\AbstractTestCase;
use Mockery;
use Mockery\MockInterface;
use OutOfBoundsException;

/**
 * Class CodeShiftTest.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class CodeShiftTest extends AbstractTestCase
{
    private CodeShift $codeShift;
    private MockInterface&DelegatingShiftInterface $delegatingShift;
    private MockInterface&DenyListInterface $denyList;
    private MockInterface&ShifterInterface $shifter;

    public function setUp(): void
    {
        $this->delegatingShift = mock(DelegatingShiftInterface::class);
        $this->denyList = mock(DenyListInterface::class, [
            'addFilter' => null,
            'clear' => null,
            'fileMatches' => false,
        ]);
        $this->shifter = mock(ShifterInterface::class, [
            'addShift' => null,
            'install' => null,
            'isInstalled' => false,
            'uninstall' => null,
        ]);

        $this->codeShift = new CodeShift(
            $this->denyList,
            $this->delegatingShift,
            $this->shifter
        );
    }

    public function testConstructorExcludesPhpCodeShiftItself(): void
    {
        $this->denyList->expects()
            ->addFilter(Mockery::on(fn (FileFilterInterface $filter) =>
                $filter->fileMatches(dirname(__DIR__, 2) . '/src/Shared.php')
            ))
            ->once();

        new CodeShift(
            $this->denyList,
            $this->delegatingShift,
            $this->shifter
        );
    }

    public function testConstructorExcludesCoreDependencies(): void
    {
        $this->denyList->expects()
            ->addFilter(Mockery::on(fn (FileFilterInterface $filter) =>
                $filter->fileMatches(dirname(__DIR__, 2) . '/vendor/nikic/php-parser/lib/PhpParser/Lexer.php')
            ))
            ->once();

        new CodeShift(
            $this->denyList,
            $this->delegatingShift,
            $this->shifter
        );
    }

    public function testDenyAddsTheFilterToTheDenyList(): void
    {
        $filter = mock(FileFilterInterface::class);

        $this->denyList->expects()
            ->addFilter($filter)
            ->once();

        $this->codeShift->deny($filter);
    }

    public function testExcludeComposerPackageAddsTheFilterToTheDenyListWhenInstalled(): void
    {
        $filter = mock(FileFilterInterface::class);

        $this->denyList->expects('addFilter')
            ->andReturnUsing(function (FileFilter $filter) {
                static::assertMatchesRegularExpression(
                    '#/vendor/mockery/mockery/\*\*$#',
                    $filter->getPatterns()[0]
                );
                static::assertMatchesRegularExpression(
                    '#/vendor/mockery/mockery/\*\*$#',
                    $filter->getPatterns()[1]
                );
                static::assertMatchesRegularExpression(
                    '#/vendor/mockery/mockery/\*\*$#',
                    $filter->getPatterns()[2]
                );
            })
            ->once();

        $this->codeShift->excludeComposerPackage('mockery/mockery');
    }

    public function testExcludeComposerPackageRaisesExceptionWhenPackageNotInstalled(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Package "not-installed/not-installed" is not installed');

        $this->codeShift->excludeComposerPackage('not-installed/not-installed');
    }

    public function testExcludeComposerPackageIfInstalledAddsTheFilterToTheDenyListWhenInstalled(): void
    {
        $filter = mock(FileFilterInterface::class);

        $this->denyList->expects('addFilter')
            ->andReturnUsing(function (FileFilter $filter) {
                static::assertMatchesRegularExpression(
                    '#/vendor/mockery/mockery/\*\*$#',
                    $filter->getPatterns()[0]
                );
                static::assertMatchesRegularExpression(
                    '#/vendor/mockery/mockery/\*\*$#',
                    $filter->getPatterns()[1]
                );
                static::assertMatchesRegularExpression(
                    '#/vendor/mockery/mockery/\*\*$#',
                    $filter->getPatterns()[2]
                );
            })
            ->once();

        $this->codeShift->excludeComposerPackageIfInstalled('mockery/mockery');
    }

    public function testExcludeComposerPackageIfInstalledDoesNotAddAnyFilterToTheDenyListWhenPackageNotInstalled(): void
    {
        $this->denyList->expects('addFilter')
            ->never();

        $this->codeShift->excludeComposerPackageIfInstalled('not-installed/not-installed');
    }

    public function testRegisterShiftTypeRegistersTheTypeWithTheDelegator(): void
    {
        $shiftType = mock(ShiftTypeInterface::class);

        $this->delegatingShift->expects()
            ->registerShiftType($shiftType)
            ->once();

        $this->codeShift->registerShiftType($shiftType);
    }

    public function testShiftAddsTheShiftToTheShifter(): void
    {
        $filter = mock(FileFilterInterface::class);
        $shiftSpec = mock(ShiftSpecInterface::class);

        $this->shifter->expects()
            ->addShift(Mockery::type(Shift::class))
            ->once();

        $this->codeShift->shift($shiftSpec, $filter);
    }

    public function testShiftInstallsTheShifterIfItIsCurrentlyNot(): void
    {
        $filter = mock(FileFilterInterface::class);
        $shiftSpec = mock(ShiftSpecInterface::class);

        $this->shifter->expects()
            ->install()
            ->once();

        $this->codeShift->shift($shiftSpec, $filter);
    }

    public function testShiftDoesNotInstallTheShifterIfItAlreadyIs(): void
    {
        $filter = mock(FileFilterInterface::class);
        $shiftSpec = mock(ShiftSpecInterface::class);
        $this->shifter->allows()
            ->isInstalled()
            ->andReturn(true);

        $this->shifter->expects()
            ->install()
            ->never();

        $this->codeShift->shift($shiftSpec, $filter);
    }

    public function testShiftUsesWildcardPhpExtensionIfNotSpecified(): void
    {
        $shiftSpec = mock(ShiftSpecInterface::class);

        $this->shifter->expects()
            ->addShift(Mockery::type(Shift::class))
            ->once()
            ->andReturnUsing(function (Shift $shift) {
                static::assertTrue($shift->appliesTo('/blah/blah/blah.php'));
            });

        $this->codeShift->shift($shiftSpec);
    }

    public function testUninstallUninstallsTheShifter(): void
    {
        $this->shifter->expects()
            ->uninstall()
            ->once();

        $this->codeShift->uninstall();
    }

    public function testUninstallClearsTheDenyList(): void
    {
        $this->denyList->expects()
            ->clear()
            ->once();

        $this->codeShift->uninstall();
    }
}
