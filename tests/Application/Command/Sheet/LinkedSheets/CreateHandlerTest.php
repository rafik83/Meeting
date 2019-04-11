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
use Proximum\Vimeet\Application\Command\Sheet\LinkedSheets\LinkedSheetsTypeUniquenessException;
use Proximum\Vimeet\Application\Command\Sheet\LinkedSheets\NotEnoughSheetsException;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Sheet\LinkedSheetsRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\View\SheetView;

class CreateHandlerTest extends TestCase
{
    public function testNoSheetFoundException()
    {
        $this->expectException(SheetNotFoundException::class);

        $linkedSheetsRepository = $this->prophesize(LinkedSheetsRepositoryInterface::class);
        $sheetsRepository = $this->prophesize(SheetRepositoryInterface::class);

        $event = $this->prophesize(Event::class);

        $sheetView = new SheetView(1, 'MyHeroCompany');

        $sheetsRepository->getSheetById(1)->shouldBeCalled()->willReturn(null);
        $linkedSheetsRepository->add(Argument::any())->shouldNotBeCalled();

        $create = new Create($event->reveal());
        $create->sheetViews = [$sheetView];

        $createHandler = new CreateHandler(
            $linkedSheetsRepository->reveal(),
            $sheetsRepository->reveal(),
            new \DateTime()
        );

        $createHandler->handle($create);
    }

    public function testNotUniqueType()
    {
        $this->expectException(LinkedSheetsTypeUniquenessException::class);

        $linkedSheetsRepository = $this->prophesize(LinkedSheetsRepositoryInterface::class);
        $sheetsRepository = $this->prophesize(SheetRepositoryInterface::class);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $type1 = $this->prophesize(Sheet::class);
        $type2 = $this->prophesize(Sheet::class);

        $sheet1->getType()->shouldBeCalled()->willReturn($type1->reveal());
        $sheet2->getType()->shouldBeCalled()->willReturn($type2->reveal());

        $sheet1->getLinkedSheets()->shouldBeCalled()->willReturn(null);
        $sheet2->getLinkedSheets()->shouldBeCalled()->willReturn(null);

        $event = $this->prophesize(Event::class);

        $sheetView1 = new SheetView(1, 'MyHeroCompany');
        $sheetView2 = new SheetView(2, 'Lee Grand Stan');

        $sheetsRepository->getSheetById(1)->shouldBeCalled()->willReturn($sheet1->reveal());
        $sheetsRepository->getSheetById(2)->shouldBeCalled()->willReturn($sheet2->reveal());

        $sheet1->setLinkedSheets(Argument::any())->shouldNotHaveBeenCalled();
        $sheet2->setLinkedSheets(Argument::any())->shouldNotHaveBeenCalled();
        $linkedSheetsRepository->add(Argument::any())->shouldNotHaveBeenCalled();

        $create = new Create($event->reveal());
        $create->sheetViews = [$sheetView1, $sheetView2];

        $createHandler = new CreateHandler(
            $linkedSheetsRepository->reveal(),
            $sheetsRepository->reveal(),
            new \DateTime()
        );

        $createHandler->handle($create);
    }

    public function testAlreadyLinkedSheet()
    {
        $this->expectException(AlreadyLinkedException::class);

        $linkedSheetsRepository = $this->prophesize(LinkedSheetsRepositoryInterface::class);
        $sheetsRepository = $this->prophesize(SheetRepositoryInterface::class);

        $linkedSheets = $this->prophesize(Sheet\LinkedSheets::class);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $sheet1->getLinkedSheets()->shouldBeCalled()->willReturn($linkedSheets->reveal());

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
            new \DateTime()
        );

        $createHandler->handle($create);
    }

    public function testMoreThan1Sheet()
    {
        $this->expectException(NotEnoughSheetsException::class);

        $linkedSheetsRepository = $this->prophesize(LinkedSheetsRepositoryInterface::class);
        $sheetsRepository = $this->prophesize(SheetRepositoryInterface::class);

        $sheet1 = $this->prophesize(Sheet::class);

        $type1 = $this->prophesize(Sheet::class);

        $sheet1->getType()->shouldBeCalled()->willReturn($type1->reveal());

        $sheet1->getLinkedSheets()->shouldBeCalled()->willReturn(null);

        $event = $this->prophesize(Event::class);

        $sheetView1 = new SheetView(1, 'MyHeroCompany');

        $sheetsRepository->getSheetById(1)->shouldBeCalled()->willReturn($sheet1->reveal());

        $sheet1->setLinkedSheets(Argument::any())->shouldNotHaveBeenCalled();
        $linkedSheetsRepository->add(Argument::any())->shouldNotHaveBeenCalled();

        $create = new Create($event->reveal());
        $create->sheetViews = [$sheetView1];

        $createHandler = new CreateHandler(
            $linkedSheetsRepository->reveal(),
            $sheetsRepository->reveal(),
            new \DateTime()
        );

        $createHandler->handle($create);
    }

    public function testCreate()
    {
        $linkedSheetsRepository = $this->prophesize(LinkedSheetsRepositoryInterface::class);
        $sheetsRepository = $this->prophesize(SheetRepositoryInterface::class);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $type = $this->prophesize(Sheet::class);

        $sheet1->getType()->shouldBeCalled()->willReturn($type->reveal());
        $sheet2->getType()->shouldBeCalled()->willReturn($type->reveal());

        $sheet1->getLinkedSheets()->shouldBeCalled()->willReturn(null);
        $sheet2->getLinkedSheets()->shouldBeCalled()->willReturn(null);

        $event = $this->prophesize(Event::class);

        $sheetView1 = new SheetView(1, 'MyHeroCompany');
        $sheetView2 = new SheetView(2, 'Lee Grand Stan');

        $sheetsRepository->getSheetById(1)->shouldBeCalled()->willReturn($sheet1->reveal());
        $sheetsRepository->getSheetById(2)->shouldBeCalled()->willReturn($sheet2->reveal());

        $sheet1->setLinkedSheets(Argument::any())->shouldBeCalled();
        $sheet2->setLinkedSheets(Argument::any())->shouldBeCalled();
        $linkedSheetsRepository->add(Argument::any())->shouldBeCalled();

        $create = new Create($event->reveal());
        $create->sheetViews = [$sheetView1, $sheetView2];

        $createHandler = new CreateHandler(
            $linkedSheetsRepository->reveal(),
            $sheetsRepository->reveal(),
            new \DateTime()
        );

        $createHandler->handle($create);
    }
}
