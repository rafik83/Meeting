<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\LinkedSheets;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Sheet\LinkedSheets\AlreadyLinkedException;
use Proximum\Vimeet\Application\Command\Sheet\LinkedSheets\Create;
use Proximum\Vimeet\Application\Command\Sheet\LinkedSheets\CreateHandler;
use Proximum\Vimeet\Application\Command\Sheet\LinkedSheets\HasScheduledMeetingException;
use Proximum\Vimeet\Application\Command\Sheet\LinkedSheets\LinkedSheetsTypeUniquenessException;
use Proximum\Vimeet\Application\Command\Sheet\LinkedSheets\NotEnoughSheetsException;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Sheet\LinkedSheetsRepositoryInterface;
use Proximum\Vimeet\Domain\View\SheetView;

class CreateHandlerTest extends TestCase
{
    public function testNoSheetFoundException(): void
    {
        $this->expectException(SheetNotFoundException::class);

        $linkedSheetsRepository = $this->prophesize(LinkedSheetsRepositoryInterface::class);
        $sheetsRepository = $this->prophesize(SheetRepositoryInterface::class);
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);

        $event = $this->prophesize(Event::class);

        $sheetView = new SheetView(1, 'MyHeroCompany');

        $sheetsRepository->getSheetById(1)->shouldBeCalled()->willReturn(null);
        $linkedSheetsRepository->add(Argument::any())->shouldNotBeCalled();

        $create = new Create($event->reveal());
        $create->sheetViews = [$sheetView];

        $createHandler = new CreateHandler(
            $linkedSheetsRepository->reveal(),
            $sheetsRepository->reveal(),
            new \DateTime(),
            $meetingRepository->reveal()
        );

        $createHandler->handle($create);
    }

    public function testNotUniqueType(): void
    {
        $this->expectException(LinkedSheetsTypeUniquenessException::class);

        $linkedSheetsRepository = $this->prophesize(LinkedSheetsRepositoryInterface::class);
        $sheetsRepository = $this->prophesize(SheetRepositoryInterface::class);
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);

        $sheet1->getType()->shouldBeCalled()->willReturn($type1->reveal());
        $sheet2->getType()->shouldBeCalled()->willReturn($type2->reveal());

        $sheet1->hasLinkedSheets()->shouldBeCalled()->willReturn(false);
        $sheet2->hasLinkedSheets()->shouldBeCalled()->willReturn(false);

        $event = $this->prophesize(Event::class);

        $sheetView1 = new SheetView(1, 'MyHeroCompany');
        $sheetView2 = new SheetView(2, 'Lee Grand Stan');

        $sheetsRepository->getSheetById(1)->shouldBeCalled()->willReturn($sheet1->reveal());
        $sheetsRepository->getSheetById(2)->shouldBeCalled()->willReturn($sheet2->reveal());

        $meetingRepository->hasScheduledMeeting($sheet1->reveal())->shouldBeCalled()->willReturn(false);

        $sheet1->setLinkedSheets(Argument::any())->shouldNotHaveBeenCalled();
        $sheet2->setLinkedSheets(Argument::any())->shouldNotHaveBeenCalled();
        $linkedSheetsRepository->add(Argument::any())->shouldNotHaveBeenCalled();

        $create = new Create($event->reveal());
        $create->sheetViews = [$sheetView1, $sheetView2];

        $createHandler = new CreateHandler(
            $linkedSheetsRepository->reveal(),
            $sheetsRepository->reveal(),
            new \DateTime(),
            $meetingRepository->reveal()
        );

        $createHandler->handle($create);
    }

    public function testAlreadyLinkedSheet(): void
    {
        $this->expectException(AlreadyLinkedException::class);

        $linkedSheetsRepository = $this->prophesize(LinkedSheetsRepositoryInterface::class);
        $sheetsRepository = $this->prophesize(SheetRepositoryInterface::class);
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $sheet1->hasLinkedSheets()->shouldBeCalled()->willReturn(true);

        $event = $this->prophesize(Event::class);

        $sheetView1 = new SheetView(1, 'MyHeroCompany');
        $sheetView2 = new SheetView(2, 'Lee Grand Stan');

        $sheetsRepository->getSheetById(1)->shouldBeCalled()->willReturn($sheet1->reveal());

        $sheet1->setLinkedSheets(Argument::any())->shouldNotHaveBeenCalled();
        $sheet2->setLinkedSheets(Argument::any())->shouldNotHaveBeenCalled();
        $linkedSheetsRepository->add(Argument::any())->shouldNotHaveBeenCalled();

        $create = new Create($event->reveal());
        $create->sheetViews = [$sheetView1, $sheetView2];

        $createHandler = new CreateHandler(
            $linkedSheetsRepository->reveal(),
            $sheetsRepository->reveal(),
            new \DateTime(),
            $meetingRepository->reveal()
        );

        $createHandler->handle($create);
    }

    public function testMoreThan1Sheet(): void
    {
        $this->expectException(NotEnoughSheetsException::class);

        $linkedSheetsRepository = $this->prophesize(LinkedSheetsRepositoryInterface::class);
        $sheetsRepository = $this->prophesize(SheetRepositoryInterface::class);
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);

        $sheet1 = $this->prophesize(Sheet::class);

        $type1 = $this->prophesize(Type::class);

        $sheet1->getType()->shouldBeCalled()->willReturn($type1->reveal());

        $sheet1->hasLinkedSheets()->shouldBeCalled()->willReturn(false);

        $event = $this->prophesize(Event::class);

        $sheetView1 = new SheetView(1, 'MyHeroCompany');

        $sheetsRepository->getSheetById(1)->shouldBeCalled()->willReturn($sheet1->reveal());

        $meetingRepository->hasScheduledMeeting($sheet1)->shouldBeCalled()->willReturn(false);

        $sheet1->setLinkedSheets(Argument::any())->shouldNotHaveBeenCalled();
        $linkedSheetsRepository->add(Argument::any())->shouldNotHaveBeenCalled();

        $create = new Create($event->reveal());
        $create->sheetViews = [$sheetView1];

        $createHandler = new CreateHandler(
            $linkedSheetsRepository->reveal(),
            $sheetsRepository->reveal(),
            new \DateTime(),
            $meetingRepository->reveal()
        );

        $createHandler->handle($create);
    }

    public function testHasScheduledMeeting(): void
    {
        $this->expectException(HasScheduledMeetingException::class);

        $linkedSheetsRepository = $this->prophesize(LinkedSheetsRepositoryInterface::class);
        $sheetsRepository = $this->prophesize(SheetRepositoryInterface::class);
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $type = $this->prophesize(Type::class);

        $sheet1->getType()->shouldBeCalled()->willReturn($type->reveal());
        $sheet2->getType()->shouldBeCalled()->willReturn($type->reveal());

        $sheet1->hasLinkedSheets()->shouldBeCalled()->willReturn(false);
        $sheet2->hasLinkedSheets()->shouldBeCalled()->willReturn(false);

        $event = $this->prophesize(Event::class);

        $sheetView1 = new SheetView(1, 'MyHeroCompany');
        $sheetView2 = new SheetView(2, 'Lee Grand Stan');

        $sheetsRepository->getSheetById(1)->shouldBeCalled()->willReturn($sheet1->reveal());
        $sheetsRepository->getSheetById(2)->shouldBeCalled()->willReturn($sheet2->reveal());

        $meetingRepository->hasScheduledMeeting($sheet1->reveal())->shouldBeCalled()->willReturn(false);
        $meetingRepository->hasScheduledMeeting($sheet2->reveal())->shouldBeCalled()->willReturn(true);

        $sheet1->setLinkedSheets(Argument::any())->shouldNotHaveBeenCalled();
        $sheet2->setLinkedSheets(Argument::any())->shouldNotHaveBeenCalled();
        $linkedSheetsRepository->add(Argument::any())->shouldNotHaveBeenCalled();

        $create = new Create($event->reveal());
        $create->sheetViews = [$sheetView1, $sheetView2];

        $createHandler = new CreateHandler(
            $linkedSheetsRepository->reveal(),
            $sheetsRepository->reveal(),
            new \DateTime(),
            $meetingRepository->reveal()
        );

        $createHandler->handle($create);
    }

    public function testCreate(): void
    {
        $linkedSheetsRepository = $this->prophesize(LinkedSheetsRepositoryInterface::class);
        $sheetsRepository = $this->prophesize(SheetRepositoryInterface::class);
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $type = $this->prophesize(Type::class);

        $sheet1->getType()->shouldBeCalled()->willReturn($type->reveal());
        $sheet2->getType()->shouldBeCalled()->willReturn($type->reveal());

        $sheet1->hasLinkedSheets()->shouldBeCalled()->willReturn(false);
        $sheet2->hasLinkedSheets()->shouldBeCalled()->willReturn(false);

        $event = $this->prophesize(Event::class);

        $sheetView1 = new SheetView(1, 'MyHeroCompany');
        $sheetView2 = new SheetView(2, 'Lee Grand Stan');

        $sheetsRepository->getSheetById(1)->shouldBeCalled()->willReturn($sheet1->reveal());
        $sheetsRepository->getSheetById(2)->shouldBeCalled()->willReturn($sheet2->reveal());

        $meetingRepository->hasScheduledMeeting($sheet1->reveal())->shouldBeCalled()->willReturn(false);
        $meetingRepository->hasScheduledMeeting($sheet2->reveal())->shouldBeCalled()->willReturn(false);

        $sheet1->setLinkedSheets(Argument::any())->shouldBeCalled();
        $sheet2->setLinkedSheets(Argument::any())->shouldBeCalled();
        $linkedSheetsRepository->add(Argument::any())->shouldBeCalled();

        $create = new Create($event->reveal());
        $create->sheetViews = [$sheetView1, $sheetView2];

        $createHandler = new CreateHandler(
            $linkedSheetsRepository->reveal(),
            $sheetsRepository->reveal(),
            new \DateTime(),
            $meetingRepository->reveal()
        );

        $createHandler->handle($create);
    }
}
