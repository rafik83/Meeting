<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Happening;

use Proximum\Vimeet\Application\Command\Happening\Participate;
use Proximum\Vimeet\Application\Command\Happening\ParticipateHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Happening\ParticipateEvent;
use Proximum\Vimeet\Application\Event\Happening\ParticipateHappeningEvent;
use Proximum\Vimeet\Application\Event\Happening\UnParticipateHappeningEvent;
use Proximum\Vimeet\Application\Exception\Happening\NotEnoughtRemainingParticipationsException;
use Proximum\Vimeet\Application\Exception\Happening\ParticipantNotAvailableException;
use Proximum\Vimeet\Application\Exception\Happening\ParticipantRequiredException;
use Proximum\Vimeet\Domain\Happening\ParticipationCount;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Domain\Model\Happening\Question;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use PHPUnit\Framework\TestCase;

class ParticipateHandlerTest extends TestCase
{
    public function testNotEnoughtRemainingParticipationsException()
    {
        $event    = EventFactory::createEvent();
        $datetime = new \DateTime();

        $user  = new User('user@vimeet.com', 'salt', 'password', 'fr');
        $sheet = SheetFactory::create($event, $user);

        $participant = ParticipantFactory::create($sheet, $user);

        $happening = new Happening(
            $event,
            new \DateTime('2016-01-01 08:00:00'),
            new \DateTime('2016-01-01 09:00:00'),
            new Category($event, 'picto', 1, '#000000', '#000000'),
            [],
            false,
            10
        );

        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $participantRepository            = $this->prophesize(ParticipantRepositoryInterface::class);
        $questionRepository               = $this->prophesize(QuestionRepositoryInterface::class);
        $participationCount               = $this->prophesize(ParticipationCount::class);
        $eventDispatcher                  = $this->prophesize(DelayedEventDispatcher::class);

        $participationCount->getRemaining($happening)->shouldBeCalled()->willReturn(0);
        $this->expectException(NotEnoughtRemainingParticipationsException::class);

        $participate = new Participate($happening, $sheet, $user, [$participant]);

        $handler = new ParticipateHandler(
            $happeningParticipationRepository->reveal(),
            $participantRepository->reveal(),
            $questionRepository->reveal(),
            $participationCount->reveal(),
            $eventDispatcher->reveal(),
            $datetime
        );

        $handler->handle($participate);
    }

    public function testParticipantNotAvailableException()
    {
        $event    = EventFactory::createEvent();
        $datetime = new \DateTime();

        $user  = new User('user@vimeet.com', 'salt', 'password', 'fr');
        $sheet = SheetFactory::create($event, $user);

        $participant = ParticipantFactory::create($sheet, $user);

        $happening = new Happening(
            $event,
            new \DateTime('2016-01-01 08:00:00'),
            new \DateTime('2016-01-01 09:00:00'),
            new Category($event, 'picto', 1, '#000000', '#000000'),
            [],
            false,
            10
        );

        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $participantRepository            = $this->prophesize(ParticipantRepositoryInterface::class);
        $questionRepository               = $this->prophesize(QuestionRepositoryInterface::class);
        $participationCount               = $this->prophesize(ParticipationCount::class);
        $eventDispatcher                  = $this->prophesize(DelayedEventDispatcher::class);

        $participationCount->getRemaining($happening)->shouldBeCalled()->willReturn(10);
        $participantRepository->getParticipantsForHappening($sheet, $happening)->shouldBeCalled()->willReturn([]);
        $participantRepository
            ->getAvailableParticipantsForHappening([$participant], $happening)
            ->shouldBeCalled()
            ->willReturn([]);

        $this->expectException(ParticipantNotAvailableException::class);

        $participate = new Participate($happening, $sheet, $user, [$participant]);

        $handler = new ParticipateHandler(
            $happeningParticipationRepository->reveal(),
            $participantRepository->reveal(),
            $questionRepository->reveal(),
            $participationCount->reveal(),
            $eventDispatcher->reveal(),
            $datetime
        );

        $handler->handle($participate);
    }

    public function testParticipantRequiredException()
    {
        $event    = EventFactory::createEvent();
        $datetime = new \DateTime();

        $user  = new User('user@vimeet.com', 'salt', 'password', 'fr');
        $sheet = SheetFactory::create($event, $user);

        $happening = new Happening(
            $event,
            new \DateTime('2016-01-01 08:00:00'),
            new \DateTime('2016-01-01 09:00:00'),
            new Category($event, 'picto', 1, '#000000', '#000000'),
            [],
            false,
            10
        );

        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $participantRepository            = $this->prophesize(ParticipantRepositoryInterface::class);
        $questionRepository               = $this->prophesize(QuestionRepositoryInterface::class);
        $participationCount               = $this->prophesize(ParticipationCount::class);
        $eventDispatcher                  = $this->prophesize(DelayedEventDispatcher::class);

        $this->expectException(ParticipantRequiredException::class);

        $participate = new Participate($happening, $sheet, $user, []);

        $handler = new ParticipateHandler(
            $happeningParticipationRepository->reveal(),
            $participantRepository->reveal(),
            $questionRepository->reveal(),
            $participationCount->reveal(),
            $eventDispatcher->reveal(),
            $datetime
        );

        $handler->handle($participate);
    }

    public function testUnparticipateAlone()
    {
        $event    = EventFactory::createEvent();
        $datetime = new \DateTime();

        $user  = new User('user@vimeet.com', 'salt', 'password', 'fr');
        $sheet = SheetFactory::create($event, $user);

        $participant = ParticipantFactory::create($sheet, $user);

        $happening = new Happening(
            $event,
            new \DateTime('2016-01-01 08:00:00'),
            new \DateTime('2016-01-01 09:00:00'),
            new Category($event, 'picto', 1, '#000000', '#000000'),
            [],
            false,
            10
        );

        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $participantRepository            = $this->prophesize(ParticipantRepositoryInterface::class);
        $questionRepository               = $this->prophesize(QuestionRepositoryInterface::class);
        $participationCount               = $this->prophesize(ParticipationCount::class);
        $eventDispatcher                  = $this->prophesize(DelayedEventDispatcher::class);

        $participationCount->getRemaining($happening)->shouldBeCalled()->willReturn(10);
        $participantRepository
            ->getParticipantsForHappening($sheet, $happening)
            ->shouldBeCalled()
            ->willReturn([$participant]);

        $happeningParticipationRepository->removeUserForHappening($user, $happening)->shouldBeCalled();

        $questionRepository->removeQuestionFromUserForHappening()->shouldNotBeCalled();
        $questionRepository->add()->shouldNotBeCalled();

        $eventDispatcher->dispatch(Events::HAPPENING_PARTICIPATED, new ParticipateEvent($sheet, [], $happening))
            ->shouldBeCalled();
        $eventDispatcher->dispatch(Events::HAPPENING_UN_PARTICIPATE, new UnParticipateHappeningEvent($participant))
            ->shouldBeCalled();

        $participate = new Participate($happening, $sheet, $user, []);

        $handler = new ParticipateHandler(
            $happeningParticipationRepository->reveal(),
            $participantRepository->reveal(),
            $questionRepository->reveal(),
            $participationCount->reveal(),
            $eventDispatcher->reveal(),
            $datetime
        );

        $handler->handle($participate);
    }

    public function testParticipateAlone()
    {
        $event    = EventFactory::createEvent();
        $datetime = new \DateTime();

        $user  = new User('user@vimeet.com', 'salt', 'password', 'fr');
        $sheet = SheetFactory::create($event, $user);

        $participant = ParticipantFactory::create($sheet, $user);

        $happening = new Happening(
            $event,
            new \DateTime('2016-01-01 08:00:00'),
            new \DateTime('2016-01-01 09:00:00'),
            new Category($event, 'picto', 1, '#000000', '#000000'),
            [],
            false,
            10
        );

        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $participantRepository            = $this->prophesize(ParticipantRepositoryInterface::class);
        $questionRepository               = $this->prophesize(QuestionRepositoryInterface::class);
        $participationCount               = $this->prophesize(ParticipationCount::class);
        $eventDispatcher                  = $this->prophesize(DelayedEventDispatcher::class);

        $participationCount->getRemaining($happening)->shouldBeCalled()->willReturn(10);
        $participantRepository
            ->getParticipantsForHappening($sheet, $happening)
            ->shouldBeCalled()
            ->willReturn([]);
        $participantRepository
            ->getAvailableParticipantsForHappening([$participant], $happening)
            ->shouldBeCalled()
            ->willReturn([$participant]);

        $happeningParticipationRepository->findByHappeningAndUser(
            $happening,
            $user
        )->shouldBeCalled()->willReturn(null);

        $happeningParticipationRepository->add(
            new HappeningParticipation($happening, $user)
        )->shouldBeCalled();

        $questionRepository->removeQuestionFromUserForHappening()->shouldNotBeCalled();
        $questionRepository->add()->shouldNotBeCalled();

        $eventDispatcher->dispatch(Events::HAPPENING_PARTICIPATED, new ParticipateEvent($sheet, [$participant], $happening))
            ->shouldBeCalled();
        $eventDispatcher->dispatch(Events::HAPPENING_PARTICIPATE, new ParticipateHappeningEvent($participant))
            ->shouldBeCalled();

        $participate = new Participate($happening, $sheet, $user, [$participant]);

        $handler = new ParticipateHandler(
            $happeningParticipationRepository->reveal(),
            $participantRepository->reveal(),
            $questionRepository->reveal(),
            $participationCount->reveal(),
            $eventDispatcher->reveal(),
            $datetime
        );

        $handler->handle($participate);
    }

    public function testParticipateSeveralParticipants()
    {
        $event    = EventFactory::createEvent();
        $datetime = new \DateTime();

        $user1 = new User('user1@vimeet.com', 'salt', 'password', 'fr');
        $user2 = new User('user2@vimeet.com', 'salt', 'password', 'fr');

        $sheet = SheetFactory::create($event, $user1);

        $participant1 = ParticipantFactory::create($sheet, $user1);
        $participant2 = ParticipantFactory::create($sheet, $user2);

        $happening = new Happening(
            $event,
            new \DateTime('2016-01-01 08:00:00'),
            new \DateTime('2016-01-01 09:00:00'),
            new Category($event, 'picto', 1, '#000000', '#000000'),
            [],
            false,
            10
        );

        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $participantRepository            = $this->prophesize(ParticipantRepositoryInterface::class);
        $questionRepository               = $this->prophesize(QuestionRepositoryInterface::class);
        $participationCount               = $this->prophesize(ParticipationCount::class);
        $eventDispatcher                  = $this->prophesize(DelayedEventDispatcher::class);

        $participationCount->getRemaining($happening)->shouldBeCalled()->willReturn(10);
        $participantRepository
            ->getParticipantsForHappening($sheet, $happening)
            ->shouldBeCalled()
            ->willReturn([]);
        $participantRepository
            ->getAvailableParticipantsForHappening([$participant1, $participant2], $happening)
            ->shouldBeCalled()
            ->willReturn([$participant1, $participant2]);

        $happeningParticipationRepository->findByHappeningAndUser(
            $happening,
            $user1
        )->shouldBeCalled()->willReturn(null);

        $happeningParticipationRepository->findByHappeningAndUser(
            $happening,
            $user2
        )->shouldBeCalled()->willReturn(null);

        $participate = new Participate($happening, $sheet, $user1, [$participant1, $participant2]);

        $happeningParticipationRepository->add(
            new HappeningParticipation($happening, $user1)
        )->shouldBeCalled();

        $happeningParticipationRepository->add(
            new HappeningParticipation($happening, $user2)
        )->shouldBeCalled();

        $questionRepository->removeQuestionFromUserForHappening()->shouldNotBeCalled();
        $questionRepository->add()->shouldNotBeCalled();

        $eventDispatcher->dispatch(Events::HAPPENING_PARTICIPATED, new ParticipateEvent($sheet, [
            $participant1,
            $participant2,
        ], $happening))->shouldBeCalled();
        $eventDispatcher->dispatch(Events::HAPPENING_PARTICIPATE, new ParticipateHappeningEvent($participant1))
            ->shouldBeCalled();
        $eventDispatcher->dispatch(Events::HAPPENING_PARTICIPATE, new ParticipateHappeningEvent($participant2))
            ->shouldBeCalled();

        $handler = new ParticipateHandler(
            $happeningParticipationRepository->reveal(),
            $participantRepository->reveal(),
            $questionRepository->reveal(),
            $participationCount->reveal(),
            $eventDispatcher->reveal(),
            $datetime
        );

        $handler->handle($participate);
    }

    public function testUpdateParticipation()
    {
        $event    = EventFactory::createEvent();
        $datetime = new \DateTime();

        $user1 = new User('user1@vimeet.com', 'salt', 'password', 'fr');
        $user2 = new User('user2@vimeet.com', 'salt', 'password', 'fr');

        $sheet = SheetFactory::create($event, $user1);

        $participant1 = ParticipantFactory::create($sheet, $user1);
        $participant2 = ParticipantFactory::create($sheet, $user2);

        $happening = new Happening(
            $event,
            new \DateTime('2016-01-01 08:00:00'),
            new \DateTime('2016-01-01 09:00:00'),
            new Category($event, 'picto', 1, '#000000', '#000000'),
            [],
            false,
            10
        );

        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $participantRepository            = $this->prophesize(ParticipantRepositoryInterface::class);
        $questionRepository               = $this->prophesize(QuestionRepositoryInterface::class);
        $participationCount               = $this->prophesize(ParticipationCount::class);
        $eventDispatcher                  = $this->prophesize(DelayedEventDispatcher::class);

        $participationCount->getRemaining($happening)->shouldBeCalled()->willReturn(10);

        // Participant1 was participating
        $participantRepository
            ->getParticipantsForHappening($sheet, $happening)
            ->shouldBeCalled()
            ->willReturn([$participant1]);

        // We remove Participant1 and replace it by Participant2
        $participantRepository
            ->getAvailableParticipantsForHappening([$participant2], $happening)
            ->shouldBeCalled()
            ->willReturn([$participant1, $participant2]);
        $participate = new Participate($happening, $sheet, $user1, [$participant2]);

        $happeningParticipationRepository->removeUserForHappening($user1, $happening)->shouldBeCalled();

        $happeningParticipationRepository->findByHappeningAndUser(
            $happening,
            $user2
        )->shouldBeCalled()->willReturn(null);

        $happeningParticipationRepository->add(
            new HappeningParticipation($happening, $user2)
        )->shouldBeCalled();

        $questionRepository->removeQuestionFromUserForHappening()->shouldNotBeCalled();
        $questionRepository->add()->shouldNotBeCalled();

        $eventDispatcher->dispatch(Events::HAPPENING_PARTICIPATED, new ParticipateEvent($sheet, [$participant2], $happening))
            ->shouldBeCalled();
        $eventDispatcher->dispatch(Events::HAPPENING_UN_PARTICIPATE, new UnParticipateHappeningEvent($participant1))
            ->shouldBeCalled();
        $eventDispatcher->dispatch(Events::HAPPENING_PARTICIPATE, new ParticipateHappeningEvent($participant2))
            ->shouldBeCalled();

        $handler = new ParticipateHandler(
            $happeningParticipationRepository->reveal(),
            $participantRepository->reveal(),
            $questionRepository->reveal(),
            $participationCount->reveal(),
            $eventDispatcher->reveal(),
            $datetime
        );

        $handler->handle($participate);
    }

    public function testParticipateWithNoQuestion()
    {
        $event    = EventFactory::createEvent();
        $datetime = new \DateTime();

        $user  = new User('user@vimeet.com', 'salt', 'password', 'fr');
        $sheet = SheetFactory::create($event, $user);

        $participant = ParticipantFactory::create($sheet, $user);

        $happening = new Happening(
            $event,
            new \DateTime('2016-01-01 08:00:00'),
            new \DateTime('2016-01-01 09:00:00'),
            new Category($event, 'picto', 1, '#000000', '#000000'),
            [],
            true,
            10
        );

        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $participantRepository            = $this->prophesize(ParticipantRepositoryInterface::class);
        $questionRepository               = $this->prophesize(QuestionRepositoryInterface::class);
        $participationCount               = $this->prophesize(ParticipationCount::class);
        $eventDispatcher                  = $this->prophesize(DelayedEventDispatcher::class);

        $participationCount->getRemaining($happening)->shouldBeCalled()->willReturn(10);
        $participantRepository
            ->getParticipantsForHappening($sheet, $happening)
            ->shouldBeCalled()
            ->willReturn([]);
        $participantRepository
            ->getAvailableParticipantsForHappening([$participant], $happening)
            ->shouldBeCalled()
            ->willReturn([$participant]);

        $happeningParticipationRepository->findByHappeningAndUser(
            $happening,
            $user
        )->shouldBeCalled()->willReturn(null);

        $happeningParticipationRepository->add(
            new HappeningParticipation($happening, $user)
        )->shouldBeCalled();

        $questionRepository->removeQuestionFromUserForHappening($user, $happening)->shouldBeCalled();
        $questionRepository->add()->shouldNotBeCalled();

        $eventDispatcher->dispatch(Events::HAPPENING_PARTICIPATED, new ParticipateEvent($sheet, [$participant], $happening))
            ->shouldBeCalled();
        $eventDispatcher->dispatch(Events::HAPPENING_PARTICIPATE, new ParticipateHappeningEvent($participant))
            ->shouldBeCalled();

        $participate = new Participate($happening, $sheet, $user, [$participant]);

        $handler = new ParticipateHandler(
            $happeningParticipationRepository->reveal(),
            $participantRepository->reveal(),
            $questionRepository->reveal(),
            $participationCount->reveal(),
            $eventDispatcher->reveal(),
            $datetime
        );

        $handler->handle($participate);
    }

    public function testParticipateWithQuestion()
    {
        $event    = EventFactory::createEvent();
        $datetime = new \DateTime();

        $user  = new User('user@vimeet.com', 'salt', 'password', 'fr');
        $sheet = SheetFactory::create($event, $user);

        $participant = ParticipantFactory::create($sheet, $user);

        $happening = new Happening(
            $event,
            new \DateTime('2016-01-01 08:00:00'),
            new \DateTime('2016-01-01 09:00:00'),
            new Category($event, 'picto', 1, '#000000', '#000000'),
            [],
            true,
            10
        );

        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $participantRepository            = $this->prophesize(ParticipantRepositoryInterface::class);
        $questionRepository               = $this->prophesize(QuestionRepositoryInterface::class);
        $participationCount               = $this->prophesize(ParticipationCount::class);
        $eventDispatcher                  = $this->prophesize(DelayedEventDispatcher::class);

        $participationCount->getRemaining($happening)->shouldBeCalled()->willReturn(10);
        $participantRepository
            ->getParticipantsForHappening($sheet, $happening)
            ->shouldBeCalled()
            ->willReturn([]);
        $participantRepository
            ->getAvailableParticipantsForHappening([$participant], $happening)
            ->shouldBeCalled()
            ->willReturn([$participant]);

        $happeningParticipationRepository->findByHappeningAndUser(
            $happening,
            $user
        )->shouldBeCalled()->willReturn(null);

        $happeningParticipationRepository->add(
            new HappeningParticipation($happening, $user)
        )->shouldBeCalled();

        $questionRepository->removeQuestionFromUserForHappening($user, $happening)->shouldBeCalled();
        $questionRepository->add(
            new Question(
                $happening,
                $sheet,
                $user,
                $datetime,
                'My question is...'
            )
        )->shouldBeCalled();

        $eventDispatcher->dispatch(Events::HAPPENING_PARTICIPATED, new ParticipateEvent($sheet, [$participant], $happening))
            ->shouldBeCalled();
        $eventDispatcher->dispatch(Events::HAPPENING_PARTICIPATE, new ParticipateHappeningEvent($participant))
            ->shouldBeCalled();

        $participate = new Participate($happening, $sheet, $user, [$participant], 'My question is...');

        $handler = new ParticipateHandler(
            $happeningParticipationRepository->reveal(),
            $participantRepository->reveal(),
            $questionRepository->reveal(),
            $participationCount->reveal(),
            $eventDispatcher->reveal(),
            $datetime
        );

        $handler->handle($participate);
    }

    public function testUnparticipateAndRemoveQuestion()
    {
        $event    = EventFactory::createEvent();
        $datetime = new \DateTime();

        $user  = new User('user@vimeet.com', 'salt', 'password', 'fr');
        $sheet = SheetFactory::create($event, $user);

        $participant = ParticipantFactory::create($sheet, $user);

        $happening = new Happening(
            $event,
            new \DateTime('2016-01-01 08:00:00'),
            new \DateTime('2016-01-01 09:00:00'),
            new Category($event, 'picto', 1, '#000000', '#000000'),
            [],
            true,
            10
        );

        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $participantRepository            = $this->prophesize(ParticipantRepositoryInterface::class);
        $questionRepository               = $this->prophesize(QuestionRepositoryInterface::class);
        $participationCount               = $this->prophesize(ParticipationCount::class);
        $eventDispatcher                  = $this->prophesize(DelayedEventDispatcher::class);

        $participationCount->getRemaining($happening)->shouldBeCalled()->willReturn(10);
        $participantRepository
            ->getParticipantsForHappening($sheet, $happening)
            ->shouldBeCalled()
            ->willReturn([$participant]);

        $happeningParticipationRepository->findByHappeningAndUser(
            $happening,
            $user
        )->shouldNotBeCalled();

        $happeningParticipationRepository->removeUserForHappening($user, $happening)->shouldBeCalled();

        $questionRepository->removeQuestionFromUserForHappening($user, $happening)->shouldBeCalled();
        $questionRepository->add()->shouldNotBeCalled();

        $eventDispatcher->dispatch(Events::HAPPENING_PARTICIPATED, new ParticipateEvent($sheet, [], $happening))
            ->shouldBeCalled();
        $eventDispatcher->dispatch(Events::HAPPENING_UN_PARTICIPATE, new UnParticipateHappeningEvent($participant))
            ->shouldBeCalled();

        $participate = new Participate($happening, $sheet, $user, []);

        $handler = new ParticipateHandler(
            $happeningParticipationRepository->reveal(),
            $participantRepository->reveal(),
            $questionRepository->reveal(),
            $participationCount->reveal(),
            $eventDispatcher->reveal(),
            $datetime
        );

        $handler->handle($participate);
    }
}
