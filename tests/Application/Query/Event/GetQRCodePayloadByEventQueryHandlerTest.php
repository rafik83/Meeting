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
use Proximum\Vimeet\Application\Query\Event\GetQRCodePayloadByEventQuery;
use Proximum\Vimeet\Application\Query\Event\GetQRCodePayloadByEventQueryHandler;
use Proximum\Vimeet\Application\View\Event\QRCodePayloadListView;
use Proximum\Vimeet\Application\View\Event\QRCodePayloadView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class GetQRCodePayloadByEventQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $user1 = $this->prophesize(User::class);
        $user1->getId()->shouldBeCalled()->willReturn(1);
        $user2 = $this->prophesize(User::class);
        $user2->getId()->shouldBeCalled()->willReturn(2);

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getUser()->shouldBeCalled()->willReturn($user1->reveal());
        $participant2 = $this->prophesize(Participant::class);
        $participant2->getUser()->shouldBeCalled()->willReturn($user1->reveal());
        $participant3 = $this->prophesize(Participant::class);
        $participant3->getUser()->shouldBeCalled()->willReturn($user2->reveal());

        $event = $this->prophesize(Event::class);
        $queryBus = $this->prophesize(QueryBusInterface::class);
        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);

        $queryBus->handle(new QRCodeIdentifierQuery($event->reveal(), $user1->reveal()))
            ->shouldBeCalled()
            ->willReturn('00000010000002');

        $queryBus->handle(new QRCodeIdentifierQuery($event->reveal(), $user2->reveal()))
            ->shouldBeCalled()
            ->willReturn('00000020000003');

        $participantRepository->findByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([$participant1->reveal(), $participant2->reveal(), $participant3->reveal()]);

        $handler = new GetQRCodePayloadByEventQueryHandler(
            $queryBus->reveal(),
            $participantRepository->reveal()
        );

        $expectedResult = new QRCodePayloadListView([
            new QRCodePayloadView('00000010000002'),
            new QRCodePayloadView('00000020000003'),
        ]);

        $result = $handler->handle(new GetQRCodePayloadByEventQuery($event->reveal()));
        $this->assertEquals($expectedResult, $result);
    }
}
