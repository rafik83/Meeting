<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Components\Planning\Formatter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Planning\Formatter\UnallocatedFormatter;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class UnallocatedFormatterTest extends TestCase
{
    public function testFormatNoRequest()
    {
        // Context
        $sheet = $this->prophesize(Sheet::class);
        $locale = 'fr';

        // Mock
        $translator = $this->prophesize(TranslatorInterface::class);
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);

        $requestRepository
            ->getUnassignedRequestsBySheetAndEvent($sheet->reveal(), Request::STATE_APPROVED)
            ->shouldBeCalled()
            ->willReturn([])
        ;

        // Formatter
        $formatter = new UnallocatedFormatter(
            $translator->reveal(),
            $requestRepository->reveal(),
            $sheetRepository->reveal()
        );
        $result = $formatter->format($sheet->reveal(), $locale);

        $expected = '';

        $this->assertEquals($expected, $result);
    }

    public function testFormat()
    {
        // Context
        $sheet = $this->prophesize(Sheet::class);
        $locale = 'fr';

        $request1 = $this->prophesize(Request::class);
        $request2 = $this->prophesize(Request::class);
        $request3 = $this->prophesize(Request::class);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getTitle()->willReturn('sheet1');
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getTitle()->willReturn('sheet2');
        $sheet3 = $this->prophesize(Sheet::class);
        $sheet3->getTitle()->willReturn('sheet3');

        $request1->getSheetMet($sheet->reveal())->shouldBeCalled()->willReturn($sheet1->reveal());
        $request2->getSheetMet($sheet->reveal())->shouldBeCalled()->willReturn($sheet2->reveal());
        $request3->getSheetMet($sheet->reveal())->shouldBeCalled()->willReturn($sheet3->reveal());

        // Mock
        $translator = $this->prophesize(TranslatorInterface::class);
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);

        $translator
            ->trans(
                UnallocatedFormatter::TRANSLATE_UNALLOCATED,
                [],
                UnallocatedFormatter::TRANSLATION_DOMAIN,
                $locale
            )->shouldBeCalled()
            ->willReturn('Meetings unallocated: ');

        $requestRepository
            ->getUnassignedRequestsBySheetAndEvent($sheet->reveal(), Request::STATE_APPROVED)
            ->shouldBeCalled()
            ->willReturn([$request1->reveal(), $request2->reveal(), $request3->reveal()])
        ;

        // Formatter
        $formatter = new UnallocatedFormatter(
            $translator->reveal(),
            $requestRepository->reveal(),
            $sheetRepository->reveal()
        );
        $result = $formatter->format($sheet->reveal(), $locale);

        $expected = "Meetings unallocated: \n\nsheet1, sheet2, sheet3";

        $this->assertEquals($expected, $result);
    }

    public function testFormatForUserNoRequest()
    {
        // Context
        $user = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);
        $locale = 'fr';

        // Mock
        $translator = $this->prophesize(TranslatorInterface::class);
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);

        $requestRepository
            ->getUnallocatedRequestForSheets([$sheet->reveal()])
            ->shouldBeCalled()
            ->willReturn([])
        ;
        $sheetRepository
            ->getSheetsByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet->reveal()])
        ;

        // Formatter
        $formatter = new UnallocatedFormatter(
            $translator->reveal(),
            $requestRepository->reveal(),
            $sheetRepository->reveal()
        );
        $result = $formatter->formatForUser($event->reveal(), $user->reveal(), $locale);

        $expected = '';

        $this->assertEquals($expected, $result);
    }

    public function testFormatForUser()
    {
        // Context
        $user = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);
        $sheetA = $this->prophesize(Sheet::class);
        $sheetB = $this->prophesize(Sheet::class);
        $sheetA->getTitle()->willReturn('sheetA');
        $sheetB->getTitle()->willReturn('sheetB');
        $locale = 'fr';

        $request1 = $this->prophesize(Request::class);
        $request2 = $this->prophesize(Request::class);
        $request3 = $this->prophesize(Request::class);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getTitle()->willReturn('sheet1');
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getTitle()->willReturn('sheet2');
        $sheet3 = $this->prophesize(Sheet::class);
        $sheet3->getTitle()->willReturn('sheet3');

        $request1->getSheetOfUser($user->reveal())->shouldBeCalled()->willReturn($sheetA);
        $request2->getSheetOfUser($user->reveal())->shouldBeCalled()->willReturn($sheetA);
        $request3->getSheetOfUser($user->reveal())->shouldBeCalled()->willReturn($sheetB);
        $request1->getSheetMet($sheetA->reveal())->shouldBeCalled()->willReturn($sheet1->reveal());
        $request2->getSheetMet($sheetA->reveal())->shouldBeCalled()->willReturn($sheet2->reveal());
        $request3->getSheetMet($sheetB->reveal())->shouldBeCalled()->willReturn($sheet3->reveal());

        // Mock
        $translator = $this->prophesize(TranslatorInterface::class);
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);

        $requestRepository
            ->getUnallocatedRequestForSheets([$sheetA->reveal(), $sheetB->reveal()])
            ->shouldBeCalled()
            ->willReturn([$request1->reveal(), $request2->reveal(), $request3->reveal()])
        ;
        $sheetRepository
            ->getSheetsByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheetA->reveal(), $sheetB->reveal()])
        ;
        $translator
            ->trans(
                UnallocatedFormatter::TRANSLATE_UNALLOCATED,
                [],
                UnallocatedFormatter::TRANSLATION_DOMAIN,
                $locale
            )->shouldBeCalled()
            ->willReturn('Meetings unallocated: ');

        // Formatter
        $formatter = new UnallocatedFormatter(
            $translator->reveal(),
            $requestRepository->reveal(),
            $sheetRepository->reveal()
        );
        $result = $formatter->formatForUser($event->reveal(), $user->reveal(), $locale, true);

        $expected = "Meetings unallocated: \n\nsheet1 (sheetA), sheet2 (sheetA), sheet3 (sheetB)";

        $this->assertEquals($expected, $result);
    }
}
