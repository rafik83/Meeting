<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Event;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Badge\QRCode\QRCodeIdentifierQuery;
use Proximum\Vimeet\Application\Query\Event\GetQRCodeIdentifiersByEventQuery;
use Proximum\Vimeet\Application\Query\Event\GetQRCodeIdentifiersByEventQueryHandler;
use Proximum\Vimeet\Application\View\Event\QRCodeIdentifierListView;
use Proximum\Vimeet\Application\View\Event\QRCodeIdentifierView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Domain\Service\Type\TypeNameResolver;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class GetQRCodeIdentifiersByEventQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $sheet = $this->prophesize(Sheet::class);

        $user1 = $this->prophesize(User::class);
        $user1->getId()->shouldBeCalled()->willReturn(1);
        $user2 = $this->prophesize(User::class);
        $user2->getId()->shouldBeCalled()->willReturn(2);

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getUser()->shouldBeCalled()->willReturn($user1->reveal());
        $participant1->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getUser()->shouldBeCalled()->willReturn($user1->reveal());
        $participant2->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());

        $participant3 = $this->prophesize(Participant::class);
        $participant3->getUser()->shouldBeCalled()->willReturn($user2->reveal());
        $participant3->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());

        $event = $this->prophesize(Event::class);
        $queryBus = $this->prophesize(QueryBusInterface::class);
        $scanRepository = $this->prophesize(ScanRepositoryInterface::class);
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $groupNameResolver = $this->prophesize(GroupNameResolver::class);
        $typeNameResolver = $this->prophesize(TypeNameResolver::class);
        $date = new \DateTime();

        $queryBus->handle(new QRCodeIdentifierQuery($event->reveal(), $user1->reveal()))
            ->shouldBeCalled()
            ->willReturn('00000010000002');

        $queryBus->handle(new QRCodeIdentifierQuery($event->reveal(), $user2->reveal()))
            ->shouldBeCalled()
            ->willReturn('00000020000003');

        $participantRepository->findByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([$participant1->reveal(), $participant2->reveal(), $participant3->reveal()]);

        $participantInfoGuesser->guessParticipantInfos($participant1->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn([
                "participant_firstname" => "Kylian",
                "participant_lastname" => "Mbappe"
            ]);

        $participantInfoGuesser->guessParticipantInfos($participant3->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn([
                "participant_firstname" => "Luka",
                "participant_lastname" => "Modric",
            ]);

        $groupNameResolver->resolve($event->reveal(), $user1->reveal(), [$sheet->reveal()])
            ->shouldBeCalled()
            ->willReturn('France');

        $groupNameResolver->resolve($event->reveal(), $user1->reveal(), [$sheet->reveal(), $sheet->reveal()])
            ->shouldBeCalled()
            ->willReturn('France');

        $groupNameResolver->resolve($event->reveal(), $user2->reveal(), [$sheet->reveal()])
            ->shouldBeCalled()
            ->willReturn('Croatie');
        $sheets = [$sheet->reveal()];
        $typeNameResolver->resolveWithPreloadedSheets($sheets, 'fr')
            ->shouldBeCalled()
            ->willReturn('Groupe A');

        $typeNameResolver->resolveWithPreloadedSheets($sheets, 'fr')
            ->shouldBeCalled()
            ->willReturn('Groupe B');

        $scanRepository->getScanDateByUserAndEvent($user1->reveal(), $event->reveal(), $date)
            ->shouldBeCalled()
            ->willReturn(null);

        $scanRepository->getScanDateByUserAndEvent($user2->reveal(), $event->reveal(), $date)
            ->shouldBeCalled()
            ->willReturn(null);

        $handler = new GetQRCodeIdentifiersByEventQueryHandler(
            $queryBus->reveal(),
            $participantRepository->reveal(),
            $scanRepository->reveal(),
            $participantInfoGuesser->reveal(),
            $groupNameResolver->reveal(),
            $typeNameResolver->reveal(),
            $date
        );

        $expectedResult = new QRCodeIdentifierListView([
            1 => new QRCodeIdentifierView('00000010000002', 'Kylian', 'Mbappe', 'France', 'Groupe B'),
            2 => new QRCodeIdentifierView('00000020000003', 'Luka', 'Modric', 'Croatie', 'Groupe B'),
        ]);
        $result = $handler->handle(new GetQRCodeIdentifiersByEventQuery($event->reveal(), 'fr'));

        $this->assertEquals($expectedResult, $result);
    }
}
