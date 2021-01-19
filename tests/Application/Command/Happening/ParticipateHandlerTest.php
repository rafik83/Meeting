<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Happening\Participate;
use Proximum\Vimeet\Application\Command\Happening\ParticipateHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Happening\ParticipateEvent;
use Proximum\Vimeet\Application\Event\Happening\ParticipateHappeningEvent;
use Proximum\Vimeet\Application\Event\Happening\UnParticipateHappeningEvent;
use Proximum\Vimeet\Application\Exception\Happening\MaxNumberHappeningParticipationReachedException;
use Proximum\Vimeet\Application\Exception\Happening\NotEnoughtRemainingParticipationsException;
use Proximum\Vimeet\Application\Exception\Happening\ParticipantMustHaveProductToParticipateException;
use Proximum\Vimeet\Application\Exception\Happening\ParticipantNotAvailableException;
use Proximum\Vimeet\Application\Exception\Happening\ParticipantRequiredException;
use Proximum\Vimeet\Application\Exception\Happening\WrongInvitationCodeException;
use Proximum\Vimeet\Domain\Happening\ParticipateToHappeningWithProductToBuyChecker;
use Proximum\Vimeet\Domain\Happening\ParticipationCount;
use Proximum\Vimeet\Domain\Happening\Webinar\MustBeAvailableToParticipate;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Domain\Model\Happening\Question;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
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

    /** @var ObjectProphecy */
    private $happeningParticipationRepository;
    /** @var ObjectProphecy */
    private $participantRepository;
    /** @var ObjectProphecy */
    private $questionRepository;
    /** @var ObjectProphecy */
    private $participationCount;
    /** @var ObjectProphecy */
    private $eventDispatcher;
    /** @var ObjectProphecy */
    private $participateToHappeningWithProductToBuyChecker;
    /** @var ObjectProphecy */
    private $mustBeAvailableToParticipate;

    public function setUp()
    {
        $this->event = EventFactory::createEvent();
        $this->datetime = new \DateTime('2015-12-21 09:00:00');
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
        $this->mustBeAvailableToParticipate = $this->prophesize(MustBeAvailableToParticipate::class);

        $this->handler = new ParticipateHandler(
            $this->happeningParticipationRepository->reveal(),
            $this->participantRepository->reveal(),
            $this->questionRepository->reveal(),
            $this->participateToHappeningWithProductToBuyChecker->reveal(),
            $this->mustBeAvailableToParticipate->reveal(),
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
        $this->mustBeAvailableToParticipate->isSatisfiedBy($this->happening)->willReturn(true);

        $this->expectException(ParticipantNotAvailableException::class);

        $this->handler->handle($this->participate);
    }

    public function testParticipantNotAvailableButHappeningIsRunning()
    {
        $sheet = $this->prophesize(Sheet::class);
        $user1 = $this->prophesize(User::class);
        $participant1 = $this->prophesize(Participant::class);
        $participant1->getUser()->shouldBeCalled()->willReturn($user1->reveal());
        $happening = $this->prophesize(Happening::class);
        $type = $this->prophesize(Type::class);
        $sheet->getType()->shouldBeCalled()->willReturn($type->reveal());
        $event = $this->prophesize(Event::class);
        $sheet->getEvent()->shouldBeCalled()->willReturn($event->reveal());

        $happening->isPrivate()->shouldBeCalled()->willReturn(false);
        $happening->getBegin()->willReturn(new \DateTime('2015-12-21 08:00:00'));
        $happening->getInvitationCode()->shouldNotBeCalled();

        $this->participateToHappeningWithProductToBuyChecker->canParticipate($participant1->reveal(), $happening->reveal())
            ->shouldBeCalled()
            ->willReturn(true);
        $this->participationCount->getRemaining($happening->reveal())->shouldBeCalled()->willReturn(INF);

        $this->participantRepository->getParticipantsForHappening($sheet->reveal(), $happening->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;
        $this->participantRepository
            ->getAvailableParticipantsForHappening([$participant1->reveal()], $happening->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;
        $this->mustBeAvailableToParticipate->isSatisfiedBy($happening->reveal())->willReturn(false);

        $this->happeningParticipationRepository
            ->findByHappeningAndUser($happening->reveal(), $user1->reveal())
            ->shouldBeCalled()
            ->willReturn(null);

        $type->getNumberMaxOfHappeningsPerUser()->shouldBeCalled()->willReturn(3);

        $this->happeningParticipationRepository->countByUserAndEvent($user1->reveal(), $event->reveal())->shouldBeCalled()->willReturn(2);

        $this->happeningParticipationRepository->add(
            new HappeningParticipation($happening->reveal(), $user1->reveal(), false, false)
        )->shouldBeCalled();

        $happening->isQuestionAllowed()->shouldBeCalled()->willReturn(true);
        $this->questionRepository->removeQuestionFromUserForHappening($user1->reveal(), $happening->reveal())->shouldBeCalled();

        $this->handler->handle(new Participate(
            $happening->reveal(),
            $sheet->reveal(),
            $user1->reveal(),
            [$participant1->reveal()],
            null,
            null,
            false
        ));
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
                new HappeningParticipation($this->happening, $this->user, false, true)
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
                new HappeningParticipation($this->happening, $user2, false, true)
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
                new HappeningParticipation($this->happening, $this->user, false, true)
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
                new HappeningParticipation($this->happening, $this->user, false, true)
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

    public function testMaxNumberHappeningParticipationNotReachedWithOneParticipant()
    {
        $sheet = $this->prophesize(Sheet::class);
        $user1 = $this->prophesize(User::class);
        $participant1 = $this->prophesize(Participant::class);
        $participant1->getUser()->shouldBeCalled()->willReturn($user1->reveal());
        $participants = [$participant1->reveal()];
        $happening = $this->prophesize(Happening::class);
        $type = $this->prophesize(Type::class);
        $sheet->getType()->shouldBeCalled()->willReturn($type->reveal());
        $event = $this->prophesize(Event::class);
        $sheet->getEvent()->shouldBeCalled()->willReturn($event->reveal());

        $happening->isPrivate()->shouldBeCalled()->willReturn(false);
        $happening->getBegin()->willReturn(new \DateTime('2016-01-01 09:00:00'));
        $happening->getInvitationCode()->shouldNotBeCalled();

        $this->participantRepository->getParticipantsForHappening($sheet->reveal(), $happening->reveal())->shouldBeCalled()->willReturn([]);

        $this->participantRepository->getAvailableParticipantsForHappening($participants, $happening->reveal())->shouldBeCalled()->willReturn([$participant1->reveal()]);

        $this->participationCount->getRemaining($happening->reveal())->shouldBeCalled()->willReturn(5);

        $this->participateToHappeningWithProductToBuyChecker->canParticipate($participant1->reveal(), $happening->reveal())->shouldBeCalled()->willReturn(true);

        $this->happeningParticipationRepository->findByHappeningAndUser($happening->reveal(), $user1->reveal())->shouldBeCalled()->willReturn(null);

        $type->getNumberMaxOfHappeningsPerUser()->shouldBeCalled()->willReturn(3);

        $this->happeningParticipationRepository->countByUserAndEvent($user1->reveal(), $event->reveal())->shouldBeCalled()->willReturn(2);

        $this->happeningParticipationRepository->add(
            new HappeningParticipation($happening->reveal(), $user1->reveal(), false, true)
        )->shouldBeCalled();

        $happening->isQuestionAllowed()->shouldBeCalled()->willReturn(true);
        $this->questionRepository->removeQuestionFromUserForHappening($user1->reveal(), $happening->reveal())->shouldBeCalled();

        $this->handler->handle(new Participate(
            $happening->reveal(),
            $sheet->reveal(),
            $user1->reveal(),
            [$participant1->reveal()],
            null,
            null,
            false
        ));
    }

    public function testMaxNumberHappeningParticipationReachedWithManyParticipants()
    {
        $sheet = $this->prophesize(Sheet::class);
        $user1 = $this->prophesize(User::class);
        $participant1 = $this->prophesize(Participant::class);
        $participant1->getUser()->shouldBeCalled()->willReturn($user1->reveal());
        $user2 = $this->prophesize(User::class);
        $participant2 = $this->prophesize(Participant::class);
        $participant2->getUser()->shouldBeCalled()->willReturn($user2->reveal());
        $participants = [$participant1->reveal(), $participant2->reveal()];
        $happening = $this->prophesize(Happening::class);
        $type = $this->prophesize(Type::class);
        $event = $this->prophesize(Event::class);
        $sheet->getType()->shouldBeCalled()->willReturn($type->reveal());
        $sheet->getEvent()->shouldBeCalled()->willReturn($event->reveal());

        $happening->isPrivate()->shouldBeCalled()->willReturn(false);
        $happening->getBegin()->willReturn(new \DateTime('2016-01-01 09:00:00'));
        $happening->getInvitationCode()->shouldNotBeCalled();

        $this->participantRepository->getParticipantsForHappening($sheet->reveal(), $happening->reveal())->shouldBeCalled()->willReturn([]);

        $this->participantRepository->getAvailableParticipantsForHappening($participants, $happening->reveal())->shouldBeCalled()->willReturn($participants);

        $this->participationCount->getRemaining($happening->reveal())->shouldBeCalled()->willReturn(5);

        $this->participateToHappeningWithProductToBuyChecker->canParticipate($participant1->reveal(), $happening->reveal())->shouldBeCalled()->willReturn(true);
        $this->participateToHappeningWithProductToBuyChecker->canParticipate($participant2->reveal(), $happening->reveal())->shouldBeCalled()->willReturn(true);

        $this->happeningParticipationRepository->findByHappeningAndUser($happening->reveal(), $user1->reveal())->shouldBeCalled()->willReturn(null);
        $this->happeningParticipationRepository->findByHappeningAndUser($happening->reveal(), $user2->reveal())->shouldBeCalled()->willReturn(null);

        $type->getNumberMaxOfHappeningsPerUser()->shouldBeCalled()->willReturn(3);

        $this->happeningParticipationRepository->countByUserAndEvent($user1->reveal(), $event->reveal())->shouldBeCalled()->willReturn(2);

        $this->happeningParticipationRepository->add(
            new HappeningParticipation($happening->reveal(), $user1->reveal(), false, true)
        )->shouldBeCalled();

        $this->happeningParticipationRepository->countByUserAndEvent($user2->reveal(), $event->reveal())->shouldBeCalled()->willReturn(3);

        try {
            $this->handler->handle(new Participate(
                $happening->reveal(),
                $sheet->reveal(),
                $user1->reveal(),
                $participants,
                null,
                null,
                false
            ));
        } catch (MaxNumberHappeningParticipationReachedException $maxNumberHappeningParticipationReachedException) {
            $this->assertEquals(new MaxNumberHappeningParticipationReachedException($participant2->reveal()), $maxNumberHappeningParticipationReachedException);
        }
    }
}
