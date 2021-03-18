<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\Happening\UpdateParticipation;
use Proximum\Vimeet\Application\Command\Happening\UpdateParticipationHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Happening\ParticipateEvent;
use Proximum\Vimeet\Application\Event\Happening\ParticipateHappeningEvent;
use Proximum\Vimeet\Application\Event\Happening\UnParticipateHappeningEvent;
use Proximum\Vimeet\Domain\Happening\ParticipateToHappeningWithProductToBuyChecker;
use Proximum\Vimeet\Domain\Happening\ParticipationCount;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class UpdateParticipationHandlerTest extends TestCase
{
    private $happeningParticipationRepository;
    private $participantRepository;
    private $participateToHappeningWithProductToBuyChecker;
    private $participationCount;
    private $eventDispatcher;
    private $updateParticipationHandler;
    private $happening;
    private $sheet;
    private $participant1;
    private $participant2;
    private $participant3;
    private $user1;
    private $user2;
    private $user3;

    public function setUp()
    {
        $this->user1 = $this->prophesize(User::class);
        $this->participant1 = $this->prophesize(Participant::class);
        $this->participant1->getUser()->willReturn($this->user1->reveal());

        $this->user2 = $this->prophesize(User::class);
        $this->participant2 = $this->prophesize(Participant::class);
        $this->participant2->getUser()->willReturn($this->user2->reveal());

        $this->user3 = $this->prophesize(User::class);
        $this->participant3 = $this->prophesize(Participant::class);
        $this->participant3->getUser()->willReturn($this->user3->reveal());

        $this->sheet = $this->prophesize(Sheet::class);
        $this->happening = $this->prophesize(Happening::class);

        $this->happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $this->participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $this->participateToHappeningWithProductToBuyChecker = $this->prophesize(ParticipateToHappeningWithProductToBuyChecker::class);
        $this->participationCount = $this->prophesize(ParticipationCount::class);
        $this->eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);

        $this->updateParticipationHandler = new UpdateParticipationHandler(
            $this->happeningParticipationRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->participateToHappeningWithProductToBuyChecker->reveal(),
            $this->participationCount->reveal(),
            $this->eventDispatcher->reveal()
        );
    }

    public function testPrivateHappening()
    {
        $this->happening->isPrivate()->willReturn(true);

        $this
            ->participantRepository
            ->getParticipantsForHappening(
                $this->sheet->reveal(),
                $this->happening->reveal()
            )
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this
            ->participantRepository
            ->getAvailableParticipantsForHappening(
                [
                    $this->participant1->reveal(),
                    $this->participant2->reveal(),
                    $this->participant3->reveal(),
                ],
                $this->happening->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(
                [
                    $this->participant1->reveal(),
                    $this->participant2->reveal(),
                    $this->participant3->reveal(),
                ]
            )
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate($this->participant1->reveal(), $this->happening->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate($this->participant2->reveal(), $this->happening->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate($this->participant3->reveal(), $this->happening->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->updateParticipationHandler->handle(
            new UpdateParticipation(
                $this->happening->reveal(),
                $this->sheet->reveal(),
                [
                    $this->participant1->reveal(),
                    $this->participant2->reveal(),
                    $this->participant3->reveal(),
                ]
            )
        );
    }

    public function testAddParticipantToHappeningHandle()
    {
        $this->happening->isPrivate()->willReturn(false);

        $this
            ->participantRepository
            ->getParticipantsForHappening(
                $this->sheet->reveal(),
                $this->happening->reveal()
            )
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this
            ->participantRepository
            ->getAvailableParticipantsForHappening(
                [
                    $this->participant1->reveal(),
                    $this->participant2->reveal(),
                    $this->participant3->reveal(),
                ],
                $this->happening->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(
                [
                    $this->participant1->reveal(),
                    $this->participant3->reveal(),
                ]
            )
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate($this->participant1->reveal(), $this->happening->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate($this->participant3->reveal(), $this->happening->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->participationCount
            ->getRemaining($this->happening->reveal())
            ->shouldBeCalled()
            ->willReturn(INF)
        ;

        $this
            ->happeningParticipationRepository
            ->findByHappeningAndUser(
                $this->happening->reveal(),
                $this->user1->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this
            ->happeningParticipationRepository
            ->add(new HappeningParticipation($this->happening->reveal(), $this->user1->reveal()))
            ->shouldBeCalled()
        ;

        $this
            ->eventDispatcher
            ->dispatch(
                Events::HAPPENING_PARTICIPATE,
                new ParticipateHappeningEvent($this->participant1->reveal(), $this->happening->reveal(), true)
            )
            ->shouldBeCalled()
        ;

        $happeningParticipationForUser3 = $this->prophesize(HappeningParticipation::class);
        $this
            ->happeningParticipationRepository
            ->findByHappeningAndUser(
                $this->happening->reveal(),
                $this->user3->reveal()
            )
            ->shouldBeCalled()
            ->willReturn($happeningParticipationForUser3->reveal())
        ;

        $happeningParticipationForUser3->setDisabled(false)->shouldBeCalled();
        $this
            ->happeningParticipationRepository
            ->update($happeningParticipationForUser3->reveal())
            ->shouldBeCalled()
        ;

        $this
            ->eventDispatcher
            ->dispatch(
                Events::HAPPENING_PARTICIPATE,
                new ParticipateHappeningEvent($this->participant3->reveal(), $this->happening->reveal(), true)
            )
            ->shouldBeCalled()
        ;

        $this->updateParticipationHandler->handle(
            new UpdateParticipation(
                $this->happening->reveal(),
                $this->sheet->reveal(),
                [
                    $this->participant1->reveal(),
                    $this->participant2->reveal(),
                    $this->participant3->reveal(),
                ]
            )
        );
    }

    public function testRemoveParticipantToHappeningHandle()
    {
        $this->happening->isPrivate()->willReturn(true);

        $this
            ->participantRepository
            ->getParticipantsForHappening(
                $this->sheet->reveal(),
                $this->happening->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(
                [
                    $this->participant1->reveal(),
                    $this->participant2->reveal(),
                ]
            )
        ;

        $this
            ->participantRepository
            ->getAvailableParticipantsForHappening(
                [
                    $this->participant1->reveal(),
                    $this->participant2->reveal(),
                ],
                $this->happening->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(
                [
                    $this->participant1->reveal(),
                    $this->participant2->reveal(),
                ]
            )
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate($this->participant1->reveal(), $this->happening->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate($this->participant2->reveal(), $this->happening->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this
            ->happeningParticipationRepository
            ->removeUserForHappening(
                $this->user1->reveal(),
                $this->happening->reveal()
            )
            ->shouldBeCalled()
        ;

        $this
            ->happeningParticipationRepository
            ->removeUserForHappening(
                $this->user2->reveal(),
                $this->happening->reveal()
            )
            ->shouldBeCalled()
        ;

        $this
            ->eventDispatcher
            ->dispatch(
                Events::HAPPENING_UN_PARTICIPATE,
                new UnParticipateHappeningEvent($this->participant1->reveal(), $this->happening->reveal(), true)
            )
            ->shouldBeCalled()
        ;

        $this
            ->eventDispatcher
            ->dispatch(
                Events::HAPPENING_UN_PARTICIPATE,
                new UnParticipateHappeningEvent($this->participant2->reveal(), $this->happening->reveal(), true)
            )
            ->shouldBeCalled()
        ;

        $this->updateParticipationHandler->handle(
            new UpdateParticipation(
                $this->happening->reveal(),
                $this->sheet->reveal(),
                []
            )
        );
    }

    public function testAddAndRemoveParticipantToHappeningHandle()
    {
        $this->happening->isPrivate()->willReturn(false);

        $this
            ->participantRepository
            ->getParticipantsForHappening(
                $this->sheet->reveal(),
                $this->happening->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(
                [
                    $this->participant1->reveal(),
                    $this->participant2->reveal(),
                ]
            )
        ;

        $this
            ->participantRepository
            ->getAvailableParticipantsForHappening(
                [
                    $this->participant1->reveal(),
                    $this->participant3->reveal(),
                    $this->participant2->reveal(), // this order is important as participant 2 is added to participants list
                ],
                $this->happening->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(
                [
                    $this->participant1->reveal(),
                    $this->participant2->reveal(),
                    $this->participant3->reveal(),
                ]
            )
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate($this->participant1->reveal(), $this->happening->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate($this->participant2->reveal(), $this->happening->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate($this->participant3->reveal(), $this->happening->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->participationCount
            ->getRemaining($this->happening->reveal())
            ->shouldBeCalled()
            ->willReturn(INF)
        ;

        $this
            ->happeningParticipationRepository
            ->findByHappeningAndUser(
                $this->happening->reveal(),
                $this->user3->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this
            ->happeningParticipationRepository
            ->add(new HappeningParticipation($this->happening->reveal(), $this->user3->reveal()))
            ->shouldBeCalled()
        ;

        $this
            ->eventDispatcher
            ->dispatch(
                Events::HAPPENING_PARTICIPATE,
                new ParticipateHappeningEvent($this->participant3->reveal(), $this->happening->reveal(), true)
            )
            ->shouldBeCalled()
        ;

        $this
            ->happeningParticipationRepository
            ->removeUserForHappening(
                $this->user2->reveal(),
                $this->happening->reveal()
            )
            ->shouldBeCalled()
        ;

        $this
            ->eventDispatcher
            ->dispatch(
                Events::HAPPENING_UN_PARTICIPATE,
                new UnParticipateHappeningEvent($this->participant2->reveal(), $this->happening->reveal(), true)
            )
            ->shouldBeCalled()
        ;

        $this->updateParticipationHandler->handle(
            new UpdateParticipation(
                $this->happening->reveal(),
                $this->sheet->reveal(),
                [
                    $this->participant1->reveal(),
                    $this->participant3->reveal()
                ]
            )
        );
    }

    public function testNotEnoughRemainingHappeningAvailabilityHandle()
    {
        $this->happening->isPrivate()->willReturn(false);

        $this
            ->participantRepository
            ->getParticipantsForHappening(
                $this->sheet->reveal(),
                $this->happening->reveal()
            )
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this
            ->participantRepository
            ->getAvailableParticipantsForHappening(
                [
                    $this->participant1->reveal(),
                    $this->participant2->reveal(),
                    $this->participant3->reveal(),
                ],
                $this->happening->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(
                [
                    $this->participant1->reveal(),
                    $this->participant3->reveal(),
                ]
            )
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate($this->participant1->reveal(), $this->happening->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;
        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate($this->participant3->reveal(), $this->happening->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->participationCount
            ->getRemaining($this->happening->reveal())
            ->shouldBeCalled()
            ->willReturn(1)
        ;

        $this->updateParticipationHandler->handle(
            new UpdateParticipation(
                $this->happening->reveal(),
                $this->sheet->reveal(),
                [
                    $this->participant1->reveal(),
                    $this->participant2->reveal(),
                    $this->participant3->reveal(),
                ]
            )
        );
    }
}
