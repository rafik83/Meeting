<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Happening\Participation;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Happening\Participation\DisableEnableParticipation;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class DisableEnableParticipationTest extends TestCase
{
    public function testResolveParticipations()
    {
        $event = $this->prophesize(Event::class);
        $happening = $this->prophesize(Happening::class);
        $happening->getEvent()->wilLReturn($event->reveal());
        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $user3 = $this->prophesize(User::class);
        $user4 = $this->prophesize(User::class);
        $user5 = $this->prophesize(User::class);
        $participation1 = $this->prophesize(HappeningParticipation::class);
        $participation2 = $this->prophesize(HappeningParticipation::class);
        $participation3 = $this->prophesize(HappeningParticipation::class);
        $participation4 = $this->prophesize(HappeningParticipation::class);
        $participation5 = $this->prophesize(HappeningParticipation::class);

        $participation1->getUser()->willReturn($user1->reveal());
        $participation2->getUser()->willReturn($user2->reveal());
        $participation3->getUser()->willReturn($user3->reveal());
        $participation4->getUser()->willReturn($user4->reveal());
        $participation5->getUser()->willReturn($user5->reveal());

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);
        $sheet4 = $this->prophesize(Sheet::class);
        $sheet5 = $this->prophesize(Sheet::class);
        $sheet6 = $this->prophesize(Sheet::class);
        $sheet7 = $this->prophesize(Sheet::class);

        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);
        $type3 = $this->prophesize(Type::class);
        $type4 = $this->prophesize(Type::class);

        $happening->getTypes()->willReturn([$type1->reveal(), $type2->reveal()]);

        $sheet1->getType()->willReturn($type1->reveal());
        $sheet2->getType()->willReturn($type1->reveal());
        $sheet3->getType()->willReturn($type2->reveal());
        $sheet4->getType()->willReturn($type2->reveal());
        $sheet5->getType()->willReturn($type3->reveal());
        $sheet6->getType()->willReturn($type4->reveal());
        $sheet7->getType()->willReturn($type4->reveal());
        $sheet1->attend()->willReturn(true);
        $sheet2->attend()->willReturn(true);
        $sheet3->attend()->willReturn(true);
        $sheet4->attend()->willReturn(true);
        $sheet5->attend()->willReturn(false);
        $sheet6->attend()->willReturn(true);
        $sheet7->attend()->willReturn(true);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $participationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);

        $participationRepository
            ->findByHappening($happening->reveal())
            ->shouldBeCalled()
            ->willReturn([
                $participation1->reveal(),
                $participation2->reveal(),
                $participation3->reveal(),
                $participation4->reveal(),
                $participation5->reveal(),
            ])
        ;

        $sheetRepository
            ->getSheetsByUserAndEvent($user1->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet1->reveal(), $sheet6->reveal()])
        ;

        $sheetRepository
            ->getSheetsByUserAndEvent($user2->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet5->reveal()])
        ;

        $sheetRepository
            ->getSheetsByUserAndEvent($user3->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet6->reveal(), $sheet7->reveal()])
        ;

        $sheetRepository
            ->getSheetsByUserAndEvent($user4->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet2->reveal(), $sheet3->reveal()])
        ;

        $sheetRepository
            ->getSheetsByUserAndEvent($user5->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet4->reveal()])
        ;

        $participation1->setDisabled(false)->shouldBeCalled();
        $participation2->setDisabled(true)->shouldBeCalled();
        $participation3->setDisabled(true)->shouldBeCalled();
        $participation4->setDisabled(false)->shouldBeCalled();
        $participation5->setDisabled(false)->shouldBeCalled();

        $participationRepository->update($participation1->reveal())->shouldBeCalled();
        $participationRepository->update($participation2->reveal())->shouldBeCalled();
        $participationRepository->update($participation3->reveal())->shouldBeCalled();
        $participationRepository->update($participation4->reveal())->shouldBeCalled();
        $participationRepository->update($participation5->reveal())->shouldBeCalled();

        $disableEnableParticipation = new DisableEnableParticipation(
            $participationRepository->reveal(),
            $sheetRepository->reveal()
        );

        $disableEnableParticipation->resolveParticipations($happening->reveal());
    }
}
