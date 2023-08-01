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

use Asmblah\PhpCodeShift\Shifter\Filter\DenyList;
use Asmblah\PhpCodeShift\Shifter\Filter\DenyListInterface;
use Asmblah\PhpCodeShift\Shifter\Filter\FileFilter;
use Asmblah\PhpCodeShift\Shifter\Filter\FileFilterInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\DelegatingShift;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\DelegatingShiftInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\FunctionHook\FunctionHookShiftType;
use Asmblah\PhpCodeShift\Shifter\Shift\Shift\ShiftTypeInterface;
use Asmblah\PhpCodeShift\Shifter\Shift\ShiftCollection;
use Asmblah\PhpCodeShift\Shifter\Shift\Spec\ShiftSpecInterface;
use Asmblah\PhpCodeShift\Shifter\Shifter;
use Asmblah\PhpCodeShift\Shifter\ShifterInterface;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

/**
 * Class CodeShift.
 *
 * Defines the public facade API for the library.
 *
 * @author Dan Phillimore <dan@ovms.co>
 */
class CodeShift implements CodeShiftFacadeInterface
{
    private DelegatingShiftInterface $delegatingShift;
    private DenyListInterface $denyList;
    private ShifterInterface $shifter;

    public function __construct(
        ?DenyListInterface $denyList = null,
        ?DelegatingShiftInterface $delegatingShift = null,
        ?ShifterInterface $shifter = null
    ) {
        if ($denyList === null) {
            $denyList = new DenyList();

            // Never transpile the source of PHP Code Shift itself.
            $denyList->addFilter(new FileFilter(realpath(__DIR__ . '/..') . '/src/**'));
        }

        if ($delegatingShift === null) {
            $delegatingShift = new DelegatingShift();

            $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
            $delegatingShift->registerShiftType(
                new FunctionHookShiftType(
                    $parser,
                    new Standard()
                )
            );
        }

        $this->delegatingShift = $delegatingShift;
        $this->denyList = $denyList;
        $this->shifter = $shifter ?? new Shifter(new ShiftCollection());
    }

    /**
     * @inheritDoc
     */
    public function deny(FileFilterInterface $filter): void
    {
        $this->denyList->addFilter($filter);
    }

    /**
     * @inheritDoc
     */
    public function registerShiftType(ShiftTypeInterface $shiftType): void
    {
        $this->delegatingShift->registerShiftType($shiftType);
    }

    /**
     * @inheritDoc
     */
    public function shift(ShiftSpecInterface $shiftSpec, FileFilterInterface $fileFilter): void
    {
        $this->shifter->addShift(
            new Shift(
                $this->delegatingShift,
                $shiftSpec,
                $this->denyList,
                $fileFilter
            )
        );

        if (!$this->shifter->isInstalled()) {
            $this->shifter->install();
        }
    }

    /**
     * @inheritDoc
     */
    public function uninstall(): void
    {
        $this->shifter->uninstall();
    }
}
