<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Agenda;

use DateTime;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Query\Agenda\SheetListViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\SheetListViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Admin\SheetView;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Planner\IndicatorCalculator;
use Proximum\Vimeet\Domain\Planner\IndicatorView;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class SheetListViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $user    = new User('email@email.com', 'salt', 'password', 'fr');
        $event   = EventFactory::createEvent();
        $type    = new Type($event);
        $package = new Package($event, 'title', new DateTime());
        $type->setPackage($package);
        $sheet = SheetFactory::create($event, null, null, $type);

        $participant = $this->createParticipantMock($sheet, $user, 1);
        $sheet->addParticipant($participant);

        $sheetRepository   = $this->prophesize(SheetRepositoryInterface::class);
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $sheetInfoGuesser  = $this->prophesize(SheetInfoGuesser::class);
        $routerInterface   = $this->prophesize(RouterInterface::class);

        $product = new Product($event, 'plan', 'name', 'img.png', 10, 1, 1, 1, true);
        $sheet->getPackage()->setPlans([$product]);

        $sheetRepository->getSheetsInCatalogByEvent($event)->shouldBeCalled()->willReturn([$sheet]);
        $requestRepository->countRequestSentBySheet($sheet)->shouldBeCalled()->willReturn(50);
        $requestRepository->countPropositionReceivedBySheet($sheet)->shouldBeCalled()->willReturn(100);
        $sheetInfoGuesser->guessSheetTitle($sheet, 'fr')->shouldBeCalled()->willReturn('Titre fiche');
        $meetingRepository->countByParticipant($participant)->shouldBeCalled()->willReturn(55);

        $indicatorCalculator = $this->prophesize(IndicatorCalculator::class);
        $indicatorCalculator->getIndicator($sheet)->shouldBeCalled()->willReturn(new IndicatorView(10, 2, 3, 4, 5, 6));

        $routerInterface->generate('admin_sheet_details', Argument::any())->willReturn('/my-url');

        $query   = new SheetListViewQuery($event, 'fr');
        $handler = new SheetListViewQueryHandler(
            $sheetRepository->reveal(),
            $meetingRepository->reveal(),
            $requestRepository->reveal(),
            $sheetInfoGuesser->reveal(),
            $indicatorCalculator->reveal(),
            $routerInterface->reveal()
        );

        $view = $handler->handle($query);

        $expectedView = new SheetView(
            $sheet->getId(),
            'Titre fiche',
            '',
            1,
            50,
            100,
            5,
            40,
            17,
            55,
            6,
            null,
            '/my-url'
        );

        $this->assertEquals($expectedView, $view[0]);
    }

    /**
     * @param Sheet $sheet
     * @param User  $user
     * @param       $id
     *
     * @return Participant
     */
    public function createParticipantMock(Sheet $sheet, User $user, $id)
    {
        $participant = new Participant($sheet, $user, [], false, true);
        $reflection  = new \ReflectionClass(Participant::class);

        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participant, $id);
        $property->setAccessible(false);

        return $participant;
    }
}
