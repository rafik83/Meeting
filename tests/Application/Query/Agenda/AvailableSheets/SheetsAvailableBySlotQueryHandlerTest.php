<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Agenda;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\SheetsAvailableBySlotQuery;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\SheetsAvailableBySlotQueryHandler;
use Proximum\Vimeet\Domain\Catalog\VisibleParticipationTypes;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\SlotFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class SheetsAvailableBySlotQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $sheetRepository;

    /** @var ObjectProphecy */
    private $participantRepository;

    /** @var ObjectProphecy */
    private $visibleParticipationTypes;

    /** @var ObjectProphecy */
    private $requestRepository;

    public function setUp()
    {
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $this->requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $this->visibleParticipationTypes = $this->prophesize(VisibleParticipationTypes::class);
    }

    public function testHandle()
    {
        $createdAt = new \DateTime();
        $user = UserFactory::create();
        $event = EventFactory::createEvent();
        $fromSheet = $this->prophesize(Sheet::class);
        $toSheet = $this->prophesize(Sheet::class);
        $thirdSheet = SheetFactory::create();
        $slot = SlotFactory::createSlot();
        $fromParticipants = [
            ParticipantFactory::create($fromSheet->reveal()),
            ParticipantFactory::create($fromSheet->reveal()),
        ];
        $toParticipants = [
            ParticipantFactory::create($toSheet->reveal()),
            ParticipantFactory::create($toSheet->reveal()),
        ];
        $requestForExcludedSheets = [
            new Request(
                $fromSheet->reveal(),
                $fromParticipants,
                $toSheet->reveal(),
                $toParticipants,
                $createdAt,
                $user,
                $event
            ),
            new Request(
                $toSheet->reveal(),
                $toParticipants,
                $fromSheet->reveal(),
                $fromParticipants,
                $createdAt,
                $user,
                $event
            )
        ];

        $excludedSheets = ["" => $fromSheet->reveal(), "" => $toSheet->reveal()];

        $allowedTypes = [
            new Type($event),
            new Type($event),
        ];

        $this->requestRepository
            ->getApprovedAndRefusedRequestBySheet($fromSheet->reveal())
            ->shouldBeCalled()
            ->willReturn($requestForExcludedSheets);

        $this->sheetRepository->getSheetsMetBySheet($fromSheet->reveal())
            ->shouldBeCalled()
            ->willReturn([]);

        $this->sheetRepository->getSheetsInCatalogByEvent($event, $excludedSheets)
            ->shouldBeCalled()
            ->willReturn([$thirdSheet]);
        
        $this->visibleParticipationTypes
            ->getAllowedTypesList($thirdSheet)
            ->shouldBeCalled()
            ->willReturn($allowedTypes);

        $this->participantRepository
            ->getAvailableParticipants(
                [],
                $slot->getBegin(),
                $slot->getEnd()
            )
            ->shouldBeCalled()
            ->willReturn($toParticipants);

        $query = new SheetsAvailableBySlotQuery(
            $event,
            $fromSheet->reveal(),
            $slot
        );

        $handler = new SheetsAvailableBySlotQueryHandler(
            $this->sheetRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->requestRepository->reveal(),
            $this->visibleParticipationTypes->reveal()
        );

        $result = $handler->handle($query);

        $this->assertEquals(1, $result);
    }
}
