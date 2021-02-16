<?php

namespace Proximum\Vimeet\Tests\Application\Components\Happening\Participation;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Components\Happening\Participation\DisableEnableParticipation;
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
    /** @var ObjectProphecy */
    private $sheetRepository;

    /** @var ObjectProphecy */
    private $participationRepository;

    /** @var ObjectProphecy */
    private $jobQueue;

    /** @var ObjectProphecy */
    private $event;

    public function setUp()
    {
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->participationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $this->jobQueue = $this->prophesize(JobQueueInterface::class);
        $this->event = $this->prophesize(Event::class);
    }

    public function testResolveParticipations()
    {
        $happening = $this->prophesize(Happening::class);
        $happening->getEvent()->willReturn($this->event->reveal());

        $user1          = $this->prophesize(User::class);
        $user2          = $this->prophesize(User::class);
        $user3          = $this->prophesize(User::class);
        $user4          = $this->prophesize(User::class);
        $user5          = $this->prophesize(User::class);
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

        $sheet1->getId()->willReturn(1);
        $sheet2->getId()->willReturn(2);
        $sheet3->getId()->willReturn(3);
        $sheet4->getId()->willReturn(4);
        $sheet5->getId()->willReturn(5);
        $sheet6->getId()->willReturn(6);
        $sheet7->getId()->willReturn(7);

        $this->participationRepository
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

        $this->sheetRepository
            ->getSheetsByUserAndEvent($user1->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet1->reveal(), $sheet6->reveal()])
        ;

        $this->sheetRepository
            ->getSheetsByUserAndEvent($user2->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet5->reveal()])
        ;

        $this->sheetRepository
            ->getSheetsByUserAndEvent($user3->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet6->reveal(), $sheet7->reveal()])
        ;

        $this->sheetRepository
            ->getSheetsByUserAndEvent($user4->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet2->reveal(), $sheet3->reveal()])
        ;

        $this->sheetRepository
            ->getSheetsByUserAndEvent($user5->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet4->reveal()])
        ;

        $participation1->isDisabled()->willReturn(true, false);
        $participation2->isDisabled()->willReturn(false, true);
        $participation3->isDisabled()->willReturn(false, false);
        $participation4->isDisabled()->willReturn(true, false);
        $participation5->isDisabled()->willReturn(false, false);
        $participation1->setDisabled(false)->shouldBeCalled();
        $participation2->setDisabled(true)->shouldBeCalled();
        $participation3->setDisabled(true)->shouldBeCalled();
        $participation4->setDisabled(false)->shouldBeCalled();
        $participation5->setDisabled(false)->shouldBeCalled();

        $this->participationRepository->update($participation1->reveal())->shouldBeCalled();
        $this->participationRepository->update($participation2->reveal())->shouldBeCalled();
        $this->participationRepository->update($participation4->reveal())->shouldBeCalled();
        $this->participationRepository->update($participation3->reveal())->shouldNotBeCalled();
        $this->participationRepository->update($participation5->reveal())->shouldNotBeCalled();

        $this->jobQueue->aggregateSheetAvailableSlot($sheet1->reveal())->shouldBeCalled();
        $this->jobQueue->aggregateSheetAvailableSlot($sheet2->reveal())->shouldBeCalled();
        $this->jobQueue->aggregateSheetAvailableSlot($sheet3->reveal())->shouldBeCalled();
        $this->jobQueue->aggregateSheetAvailableSlot($sheet5->reveal())->shouldBeCalled();
        $this->jobQueue->aggregateSheetAvailableSlot($sheet6->reveal())->shouldBeCalled();
        $this->jobQueue->aggregateSheetAvailableSlot($sheet4->reveal())->shouldNotBeCalled();
        $this->jobQueue->aggregateSheetAvailableSlot($sheet7->reveal())->shouldNotBeCalled();

        $disableEnableParticipation = new DisableEnableParticipation(
            $this->participationRepository->reveal(),
            $this->sheetRepository->reveal(),
            $this->jobQueue->reveal()
        );

        $disableEnableParticipation->resolveParticipations($happening->reveal());
    }

    public function testResolveParticipationsForUser()
    {
        $user = $this->prophesize(User::class);

        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);
        $type3 = $this->prophesize(Type::class);

        $participation1 = $this->prophesize(HappeningParticipation::class);
        $participation2 = $this->prophesize(HappeningParticipation::class);
        $happening1 = $this->prophesize(Happening::class);
        $happening2 = $this->prophesize(Happening::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);

        $sheet1->getId()->willReturn(1);
        $sheet2->getId()->willReturn(2);
        $sheet3->getId()->willReturn(3);
        $sheet1->attend()->willReturn(true);
        $sheet2->attend()->willReturn(false);
        $sheet3->attend()->willReturn(true);

        $sheet1->getType()->willReturn($type1->reveal());
        $sheet2->getType()->willReturn($type2->reveal());
        $sheet3->getType()->willReturn($type2->reveal());

        $this->sheetRepository
            ->getSheetsByUserAndEvent($user->reveal(), $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn([$sheet1->reveal(), $sheet2->reveal(), $sheet3->reveal()])
        ;

        $participation1->isDisabled()->willReturn(false, false);
        $participation2->isDisabled()->willReturn(false, true);
        $participation1->setDisabled(false)->shouldBeCalled();
        $participation2->setDisabled(true)->shouldBeCalled();
        $participation1->getHappening()->willReturn($happening1->reveal());
        $participation2->getHappening()->willReturn($happening2->reveal());

        $happening1->getTypes()->willReturn([$type1->reveal(), $type2->reveal()]);
        $happening2->getTypes()->willReturn([$type3->reveal()]);

        $this->participationRepository
            ->findByUser($user->reveal(), $this->event->reveal(), false)
            ->shouldBeCalled()
            ->willReturn([$participation1->reveal(), $participation2->reveal()])
        ;

        $this->participationRepository->update($participation1->reveal())->shouldNotBeCalled();
        $this->participationRepository->update($participation2->reveal())->shouldBeCalled();

        $disableEnableParticipation = new DisableEnableParticipation(
            $this->participationRepository->reveal(),
            $this->sheetRepository->reveal(),
            $this->jobQueue->reveal()
        );

        $disableEnableParticipation->resolveParticipationsForUser(
            $this->event->reveal(),
            $user->reveal()
        );
    }
}
