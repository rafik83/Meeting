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
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Query\Agenda\SheetListViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\SheetListViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\SheetView;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class SheetListViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $user = new User('email@email.com', 'salt', 'password', 'fr');
        $event = (new EventFactory())->createEvent();
        $sheet = (new SheetFactory())->create($event);

        $participant = $this->createParticipantMock($sheet, $user, 1);
        $sheet->addParticipant($participant);

        $sheetRepository          = $this->prophesize(SheetRepositoryInterface::class);
        $meetingRepository        = $this->prophesize(MeetingRepositoryInterface::class);
        $requestRepository        = $this->prophesize(RequestRepositoryInterface::class);
        $meetingSlotRepository    = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $participantRepository    = $this->prophesize(ParticipantRepositoryInterface::class);
        $unavailabilityRepository = $this->prophesize(UnavailabilityRepositoryInterface::class);
        $sheetInfoGuesser         = $this->prophesize(SheetInfoGuesser::class);

        $sheet->updateType((new Type($event))->setPackage(new Package($event, 'title', new DateTime())));

        $product = new Product($event, 'plan', 'name', 'img.png', 10, 1, 1, 1, true);
        $sheet->getPackage()->setPlans([$product]);

        $sheetRepository->getEnabledSheetsByEvent($event)->shouldBeCalled()->willReturn([$sheet]);
        $requestRepository->countRequestSentBySheet($sheet)->shouldBeCalled()->willReturn(50);
        $requestRepository->countPropositionReceivedBySheet($sheet)->shouldBeCalled()->willReturn(100);
        $participantRepository->countParticipantBySheet($sheet)->shouldBeCalled()->willReturn(5);
        $meetingSlotRepository->countByEvent($event)->shouldBeCalled()->willReturn(10);
        $sheetInfoGuesser->guessSheetTitle($sheet, 'fr')->shouldBeCalled()->willReturn('Titre fiche');
        $requestRepository->countApprovedPropositionReceivedBySheet($sheet)->shouldBeCalled()->willReturn(22);
        $unavailabilityRepository->countByParticipant($participant)->shouldBeCalled()->willReturn(1);
        $meetingRepository->countByParticipant($participant)->shouldBeCalled()->willReturn(55);

        $expectedView = new SheetView(
            $sheet->getId(),
            'Titre fiche',
            '',
            1,
            50,
            100,
            22,
            50,
            49,
            55
        );

        $query   = new SheetListViewQuery($event, 'fr');
        $handler = new SheetListViewQueryHandler(
            $sheetRepository->reveal(),
            $meetingRepository->reveal(),
            $requestRepository->reveal(),
            $meetingSlotRepository->reveal(),
            $participantRepository->reveal(),
            $unavailabilityRepository->reveal(),
            $sheetInfoGuesser->reveal()
        );

        $view = $handler->handle($query);

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
