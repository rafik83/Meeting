<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Happening;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Happening\Participate;
use Proximum\Vimeet\Application\Command\Happening\ParticipateHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Happening\ParticipateEvent;
use Proximum\Vimeet\Application\Event\Happening\ParticipateHappeningEvent;
use Proximum\Vimeet\Application\Event\Happening\UnParticipateHappeningEvent;
use Proximum\Vimeet\Application\Exception\Happening\NotEnoughtRemainingParticipationsException;
use Proximum\Vimeet\Application\Exception\Happening\ParticipantMustHaveProductToParticipateException;
use Proximum\Vimeet\Application\Exception\Happening\ParticipantNotAvailableException;
use Proximum\Vimeet\Application\Exception\Happening\ParticipantRequiredException;
use Proximum\Vimeet\Application\Exception\Happening\WrongInvitationCodeException;
use Proximum\Vimeet\Domain\Happening\ParticipateToHappeningWithProductToBuyChecker;
use Proximum\Vimeet\Domain\Happening\ParticipationCount;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Domain\Model\Happening\Question;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class ParticipateHandlerTest extends TestCase
{
    /** @var Event */
    private $event;

    /** @var \DateTimeInterface */
    private $datetime;

    /** @var User */
    private $user;

    /** @var Sheet */
    private $sheet;

    /** @var Participant */
    private $participant;

    /** @var ParticipateHandler */
    private $handler;

    /** @var Happening */
    private $happening;

    /** @var Participate */
    private $participate;

    /** @var Question */
    private $question;

    private $happeningParticipationRepository;
    private $participantRepository;
    private $questionRepository;
    private $participationCount;
    private $eventDispatcher;
    private $participateToHappeningWithProductToBuyChecker;

    public function setUp()
    {
        $this->event = EventFactory::createEvent();
        $this->datetime = new \DateTime();
        $this->user = new User('user@vimeet.com', 'salt', 'password', 'fr');
        $this->sheet = SheetFactory::create($this->event, $this->user);
        $this->participant = ParticipantFactory::create($this->sheet, $this->user);
        $this->happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $this->participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $this->questionRepository = $this->prophesize(QuestionRepositoryInterface::class);
        $this->participationCount = $this->prophesize(ParticipationCount::class);
        $this->eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);
        $this->participateToHappeningWithProductToBuyChecker = $this->prophesize(
            ParticipateToHappeningWithProductToBuyChecker::class
        );

        $this->handler = new ParticipateHandler(
            $this->happeningParticipationRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->questionRepository->reveal(),
            $this->participateToHappeningWithProductToBuyChecker->reveal(),
            $this->participationCount->reveal(),
            $this->eventDispatcher->reveal(),
            $this->datetime
        );
        $this->happening = new Happening(
            $this->event,
            new \DateTime('2016-01-01 08:00:00'),
            new \DateTime('2016-01-01 09:00:00'),
            new Category($this->event, 'picto', 1, '#000000', '#000000'),
            [],
            false,
            10
        );
        $this->participate = new Participate(
            $this->happening,
            $this->sheet,
            $this->user,
            [$this->participant],
            null,
            'tata',
            false
        );
        $this->question = new Question($this->happening, $this->sheet, $this->user, $this->datetime, 'toto');
    }

    public function testNotEnoughtRemainingParticipationsException()
    {
        $this->expectException(NotEnoughtRemainingParticipationsException::class);
        $this->participationCount->getRemaining($this->happening)->shouldBeCalled()->willReturn(0);
        $this->participantRepository
            ->getParticipantsForHappening($this->sheet, $this->happening)
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this->participantRepository
            ->getAvailableParticipantsForHappening([$this->participant], $this->happening)
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this->participate->sheet = $this->sheet;
        $this->participate->happening = $this->happening;
        $this->participate->participants = [$this->participant];

        $this->participantRepository
            ->getParticipantsForHappening($this->sheet, $this->happening)
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this->participantRepository
            ->getAvailableParticipantsForHappening([$this->participant], $this->happening)
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this->participate->sheet = $this->sheet;
        $this->participate->happening = $this->happening;
        $this->participate->participants = [$this->participant];

        $this->handler->handle($this->participate);
    }

    public function testWrongInvitationCodeException()
    {
        $this->expectException(WrongInvitationCodeException::class);
        $this->happening->setInvitationCode('toto');

        $participate = $this->participate;
        $participate->question = 'toto';

        $this->handler->handle($participate);
    }

    public function testParticipantNotAvailableException()
    {
        $this->participationCount->getRemaining($this->happening)->shouldBeCalled()->willReturn(10);
        $this->participantRepository->getParticipantsForHappening($this->sheet, $this->happening)
            ->shouldBeCalled()
            ->willReturn([])
        ;
        $this->participantRepository
            ->getAvailableParticipantsForHappening([$this->participant], $this->happening)
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this->expectException(ParticipantNotAvailableException::class);

        $this->handler->handle($this->participate);
    }

    public function testParticipantRequiredException()
    {
        $this->expectException(ParticipantRequiredException::class);

        $this->participantRepository
            ->getParticipantsForHappening($this->sheet, $this->happening)
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $participate = $this->participate;
        $participate->participants = [];

        $this->handler->handle($participate);
    }

    public function testUnparticipateAlone()
    {
        $this->participationCount->getRemaining($this->happening)->shouldBeCalled()->willReturn(10);
        $this->participantRepository
            ->getParticipantsForHappening($this->sheet, $this->happening)
            ->shouldBeCalled()
            ->willReturn([$this->participant])
        ;

        $this->happeningParticipationRepository
            ->removeUserForHappening($this->user, $this->happening)
            ->shouldBeCalled()
        ;

        $this->questionRepository->removeQuestionFromUserForHappening()->shouldNotBeCalled();
        $this->questionRepository->add()->shouldNotBeCalled();

        $this
            ->eventDispatcher
            ->dispatch(
                Events::HAPPENING_PARTICIPATED,
                new ParticipateEvent($this->sheet, [], $this->happening)
            )
            ->shouldBeCalled()
        ;
        $this
            ->eventDispatcher
            ->dispatch(
                Events::HAPPENING_UN_PARTICIPATE,
                new UnParticipateHappeningEvent($this->participant, $this->happening)
            )
            ->shouldBeCalled()
        ;

        $participate = $this->participate;
        $participate->participants = [];

        $this->handler->handle($participate);
    }

    public function testParticipateAlone()
    {
        $this->participationCount->getRemaining($this->happening)->shouldBeCalled()->willReturn(10);
        $this->participantRepository
            ->getParticipantsForHappening($this->sheet, $this->happening)
            ->shouldBeCalled()
            ->willReturn([])
        ;
        $this->participantRepository
            ->getAvailableParticipantsForHappening([$this->participant], $this->happening)
            ->shouldBeCalled()
            ->willReturn([$this->participant])
        ;

        $this->happeningParticipationRepository
            ->findByHappeningAndUser(
                $this->happening,
                $this->user
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->happeningParticipationRepository
            ->add(
                new HappeningParticipation($this->happening, $this->user)
            )
            ->shouldBeCalled()
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate($this->participant, $this->happening)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->questionRepository->removeQuestionFromUserForHappening()->shouldNotBeCalled();
        $this->questionRepository->add()->shouldNotBeCalled();

        $this
            ->eventDispatcher
            ->dispatch(
                Events::HAPPENING_PARTICIPATED,
                new ParticipateEvent($this->sheet, [$this->participant], $this->happening)
            )
            ->shouldBeCalled()
        ;
        $this
            ->eventDispatcher
            ->dispatch(
                Events::HAPPENING_PARTICIPATE,
                new ParticipateHappeningEvent($this->participant, $this->happening)
            )
            ->shouldBeCalled()
        ;

        $this->handler->handle($this->participate);
    }

    public function testParticipantMustHaveProductToParticipateException()
    {
        $this->expectException(ParticipantMustHaveProductToParticipateException::class);

        $user2 = new User('user2@vimeet.com', 'salt', 'password', 'fr');
        $participant2 = ParticipantFactory::create($this->sheet, $user2);

        $this->participationCount->getRemaining($this->happening)->shouldBeCalled()->willReturn(10);
        $this->participantRepository
            ->getParticipantsForHappening($this->sheet, $this->happening)
            ->shouldBeCalled()
            ->willReturn([])
        ;
        $this->participantRepository
            ->getAvailableParticipantsForHappening(
                [
                    $this->participant,
                    $participant2,
                ],
                $this->happening
            )
            ->shouldBeCalled()
            ->willReturn(
                [
                    $this->participant,
                    $participant2,
                ]
            )
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate($this->participant, $this->happening)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate($participant2, $this->happening)
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $participate = $this->participate;
        $participate->participants = [
            $this->participant,
            $participant2,
        ];

        $this->handler->handle($participate);
    }

    public function testParticipateSeveralParticipants()
    {
        $user2 = new User('user2@vimeet.com', 'salt', 'password', 'fr');
        $participant2 = ParticipantFactory::create($this->sheet, $user2);

        $this->participationCount->getRemaining($this->happening)->shouldBeCalled()->willReturn(10);
        $this->participantRepository
            ->getParticipantsForHappening($this->sheet, $this->happening)
            ->shouldBeCalled()
            ->willReturn([])
        ;
        $this->participantRepository
            ->getAvailableParticipantsForHappening(
                [
                    $this->participant,
                    $participant2,
                ],
                $this->happening
            )
            ->shouldBeCalled()
            ->willReturn(
                [
                    $this->participant,
                    $participant2,
                ]
            )
        ;

        $this
            ->happeningParticipationRepository
            ->findByHappeningAndUser(
                $this->happening,
                $this->user
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->happeningParticipationRepository
            ->findByHappeningAndUser(
                $this->happening,
                $user2
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate($this->participant, $this->happening)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate($participant2, $this->happening)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $participate = $this->participate;
        $participate->participants = [
            $this->participant,
            $participant2,
        ];

        $this
            ->happeningParticipationRepository
            ->add(
                new HappeningParticipation($this->happening, $this->user)
            )
            ->shouldBeCalled()
        ;

        $this
            ->happeningParticipationRepository
            ->add(
                new HappeningParticipation($this->happening, $user2)
            )
            ->shouldBeCalled()
        ;

        $this->questionRepository->removeQuestionFromUserForHappening()->shouldNotBeCalled();
        $this->questionRepository->add()->shouldNotBeCalled();

        $this
            ->eventDispatcher
            ->dispatch(
                Events::HAPPENING_PARTICIPATED,
                new ParticipateEvent(
                    $this->sheet,
                    [
                        $this->participant,
                        $participant2,
                    ],
                    $this->happening
                )
            )
            ->shouldBeCalled()
        ;
        $this
            ->eventDispatcher
            ->dispatch(
                Events::HAPPENING_PARTICIPATE,
                new ParticipateHappeningEvent($this->participant, $this->happening)
            )
            ->shouldBeCalled()
        ;
        $this->eventDispatcher
            ->dispatch(Events::HAPPENING_PARTICIPATE, new ParticipateHappeningEvent($participant2, $this->happening))
            ->shouldBeCalled()
        ;

        $this->handler->handle($participate);
    }

    public function testUpdateParticipation()
    {
        $user2 = new User('user2@vimeet.com', 'salt', 'password', 'fr');
        $participant2 = ParticipantFactory::create($this->sheet, $user2);

        $this->participationCount->getRemaining($this->happening)->shouldBeCalled()->willReturn(10);

        // Participant1 was participating
        $this->participantRepository
            ->getParticipantsForHappening($this->sheet, $this->happening)
            ->shouldBeCalled()
            ->willReturn([$this->participant])
        ;

        // We remove Participant1 and replace it by Participant2
        $this->participantRepository
            ->getAvailableParticipantsForHappening([$participant2], $this->happening)
            ->shouldBeCalled()
            ->willReturn(
                [
                    $this->participant,
                    $participant2,
                ]
            )
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate($participant2, $this->happening)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $participate = $this->participate;
        $participate->participants = [$participant2];

        $this
            ->happeningParticipationRepository
            ->removeUserForHappening($this->user, $this->happening)
            ->shouldBeCalled()
        ;

        $this
            ->happeningParticipationRepository
            ->findByHappeningAndUser(
                $this->happening,
                $user2
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this
            ->happeningParticipationRepository
            ->add(
                new HappeningParticipation($this->happening, $user2)
            )
            ->shouldBeCalled()
        ;

        $this->questionRepository->removeQuestionFromUserForHappening()->shouldNotBeCalled();
        $this->questionRepository->add()->shouldNotBeCalled();

        $this->eventDispatcher
            ->dispatch(
                Events::HAPPENING_PARTICIPATED,
                new ParticipateEvent($this->sheet, [$participant2], $this->happening)
            )
            ->shouldBeCalled()
        ;
        $this->eventDispatcher
            ->dispatch(
                Events::HAPPENING_UN_PARTICIPATE,
                new UnParticipateHappeningEvent($this->participant, $this->happening)
            )
            ->shouldBeCalled()
        ;
        $this->eventDispatcher
            ->dispatch(Events::HAPPENING_PARTICIPATE, new ParticipateHappeningEvent($participant2, $this->happening))
            ->shouldBeCalled()
        ;

        $this->handler->handle($participate);
    }

    public function testParticipateWithNoQuestion()
    {
        $this->happening->setQuestionAllowed(true);

        $this->participationCount->getRemaining($this->happening)->shouldBeCalled()->willReturn(10);
        $this
            ->participantRepository
            ->getParticipantsForHappening($this->sheet, $this->happening)
            ->shouldBeCalled()
            ->willReturn([])
        ;
        $this
            ->participantRepository
            ->getAvailableParticipantsForHappening([$this->participant], $this->happening)
            ->shouldBeCalled()
            ->willReturn([$this->participant])
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate($this->participant, $this->happening)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this
            ->happeningParticipationRepository
            ->findByHappeningAndUser(
                $this->happening,
                $this->user
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->happeningParticipationRepository
            ->add(
                new HappeningParticipation($this->happening, $this->user)
            )
            ->shouldBeCalled()
        ;

        $this->questionRepository->removeQuestionFromUserForHappening($this->user, $this->happening)->shouldBeCalled();
        $this->questionRepository->add()->shouldNotBeCalled();

        $this
            ->eventDispatcher
            ->dispatch(
                Events::HAPPENING_PARTICIPATED,
                new ParticipateEvent($this->sheet, [$this->participant], $this->happening)
            )
            ->shouldBeCalled()
        ;
        $this
            ->eventDispatcher
            ->dispatch(
                Events::HAPPENING_PARTICIPATE,
                new ParticipateHappeningEvent($this->participant, $this->happening)
            )
            ->shouldBeCalled()
        ;

        $this->handler->handle($this->participate);
    }

    public function testParticipateWithQuestion()
    {
        $this->happening->setQuestionAllowed(true);

        $this->participationCount->getRemaining($this->happening)->shouldBeCalled()->willReturn(10);
        $this->participantRepository
            ->getParticipantsForHappening($this->sheet, $this->happening)
            ->shouldBeCalled()
            ->willReturn([])
        ;
        $this->participantRepository
            ->getAvailableParticipantsForHappening([$this->participant], $this->happening)
            ->shouldBeCalled()
            ->willReturn([$this->participant])
        ;

        $this
            ->participateToHappeningWithProductToBuyChecker
            ->canParticipate($this->participant, $this->happening)
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->happeningParticipationRepository
            ->findByHappeningAndUser(
                $this->happening,
                $this->user
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this
            ->happeningParticipationRepository
            ->add(
                new HappeningParticipation($this->happening, $this->user)
            )
            ->shouldBeCalled()
        ;

        $this->questionRepository->removeQuestionFromUserForHappening($this->user, $this->happening)->shouldBeCalled();
        $this->questionRepository
            ->add(
                new Question(
                    $this->happening,
                    $this->sheet,
                    $this->user,
                    $this->datetime,
                    'My question is...'
                )
            )
            ->shouldBeCalled()
        ;

        $this
            ->eventDispatcher
            ->dispatch(
                Events::HAPPENING_PARTICIPATED,
                new ParticipateEvent($this->sheet, [$this->participant], $this->happening)
            )
            ->shouldBeCalled()
        ;
        $this
            ->eventDispatcher
            ->dispatch(
                Events::HAPPENING_PARTICIPATE,
                new ParticipateHappeningEvent($this->participant, $this->happening)
            )
            ->shouldBeCalled()
        ;

        $participate = $this->participate;
        $participate->question = 'My question is...';

        $this->handler->handle($participate);
    }

    public function testUnparticipateAndRemoveQuestion()
    {
        $this->happening->setQuestionAllowed(true);

        $this->participationCount->getRemaining($this->happening)->shouldBeCalled()->willReturn(10);
        $this->participantRepository
            ->getParticipantsForHappening($this->sheet, $this->happening)
            ->shouldBeCalled()
            ->willReturn([$this->participant])
        ;

        $this
            ->happeningParticipationRepository
            ->findByHappeningAndUser(
                $this->happening,
                $this->user
            )
            ->shouldNotBeCalled()
        ;

        $this
            ->happeningParticipationRepository
            ->removeUserForHappening($this->user, $this->happening)
            ->shouldBeCalled()
        ;

        $this->questionRepository->removeQuestionFromUserForHappening($this->user, $this->happening)->shouldBeCalled();
        $this->questionRepository->add()->shouldNotBeCalled();

        $this->eventDispatcher
            ->dispatch(
                Events::HAPPENING_PARTICIPATED,
                new ParticipateEvent($this->sheet, [], $this->happening)
            )
            ->shouldBeCalled()
        ;
        $this->eventDispatcher
            ->dispatch(
                Events::HAPPENING_UN_PARTICIPATE,
                new UnParticipateHappeningEvent($this->participant,  $this->happening)
            )
            ->shouldBeCalled()
        ;

        $participate = $this->participate;
        $participate->participants = [];

        $this->handler->handle($this->participate);
    }
}
