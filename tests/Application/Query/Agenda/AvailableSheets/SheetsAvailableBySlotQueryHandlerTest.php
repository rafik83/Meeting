<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Agenda\AvailableSheets;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\SheetsAvailableBySlotQuery;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\SheetsAvailableBySlotQueryHandler;
use Proximum\Vimeet\Domain\Catalog\VisibleParticipationTypes;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetsAvailableBySlotQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $sheetRepository;

    /** @var ObjectProphecy */
    private $participantRepository;

    /** @var ObjectProphecy */
    private $visibleParticipationTypes;

    public function setUp()
    {
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $this->visibleParticipationTypes = $this->prophesize(VisibleParticipationTypes::class);
    }

    public function testHandle()
    {
        $begin        = new \DateTime('2017-08-08 10:10:00.000');
        $end          = new \DateTime('2017-08-08 10:30:00.000');
        $event        = $this->prophesize(Event::class);
        $userSheet    = $this->prophesize(Sheet::class);
        $sheet1       = $this->prophesize(Sheet::class);
        $sheet2       = $this->prophesize(Sheet::class);
        $sheet3       = $this->prophesize(Sheet::class);
        $userSheet->getId()->willReturn(15);
        $sheet1->getId()->willReturn(21);
        $sheet2->getId()->willReturn(22);
        $sheet3->getId()->willReturn(23);
        $slot         = $this->prophesize(MeetingSlot::class);
        $slot->getBegin()->willReturn($begin);
        $slot->getEnd()->willReturn($end);
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $participant3 = $this->prophesize(Participant::class);
        $participant4 = $this->prophesize(Participant::class);
        $participant1->getSheet()->willReturn($sheet2->reveal());
        $participant2->getSheet()->willReturn($sheet2->reveal());
        $participant3->getSheet()->willReturn($sheet3->reveal());
        $participant4->getSheet()->willReturn($sheet3->reveal());
        $sheet2->getParticipants()->willReturn(new ArrayCollection([$participant1->reveal(), $participant2->reveal()]));
        $sheet3->getParticipants()->willReturn(new ArrayCollection([$participant3->reveal(), $participant4->reveal()]));
        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);

        $allowedTypes = [$type1->reveal(), $type2->reveal()];

        $this->sheetRepository->getSheetsWithRequestWithSheet($userSheet->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet1->reveal()]);

        $this->sheetRepository
            ->getSheetsInCatalogWithTypesByEvent(
                $event,
                $allowedTypes,
                [15 => $userSheet->reveal(), 21 => $sheet1->reveal()]
            )
            ->shouldBeCalled()
            ->willReturn([$sheet2->reveal(), $sheet3->reveal()]);
        
        $this->visibleParticipationTypes
            ->getAllowedTypesList($userSheet->reveal())
            ->shouldBeCalled()
            ->willReturn($allowedTypes);

        $this->participantRepository
            ->getAvailableParticipants(
                [
                    $participant1->reveal(),
                    $participant2->reveal(),
                    $participant3->reveal(),
                    $participant4->reveal(),
                ],
                $begin,
                $end
            )
            ->shouldBeCalled()
            ->willReturn([
                $participant1->reveal()
            ]);

        $query = new SheetsAvailableBySlotQuery(
            $event->reveal(),
            $userSheet->reveal(),
            $slot->reveal()
        );

        $handler = new SheetsAvailableBySlotQueryHandler(
            $this->sheetRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->visibleParticipationTypes->reveal()
        );

        $result = $handler->handle($query);

        $this->assertEquals(1, $result);
    }
}
