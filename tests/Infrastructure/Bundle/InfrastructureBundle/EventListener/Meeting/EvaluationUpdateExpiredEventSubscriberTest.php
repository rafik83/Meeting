<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\EventListener\Meeting;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Components\Rule\ParticipantInfoAccessRule;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Components\Transactional\Mail\PrepareHandler;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareMeetingFollowUpView;
use Proximum\Vimeet\Application\Event\Meeting\MeetingEvaluationUpdateExpiredEvent;
use Proximum\Vimeet\Domain\Meeting\FollowUpMailAccessRules;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Meeting\EvaluationUpdateExpiredEventSubscriber;
use Proximum\Vimeet\Tests\Factory\MeetingFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Meeting\MeetingFollowUpMail;

class EvaluationUpdateExpiredEventSubscriberTest extends TestCase
{
    private ObjectProphecy $mailer;
    private ObjectProphecy $prepareHandler;
    private ObjectProphecy $participantInfoGuesser;
    private ObjectProphecy $eventUrlGeneratorInterface;
    private ObjectProphecy $followupMailAccessRules;
    private ObjectProphecy $meetingRepository;
    private EvaluationUpdateExpiredEventSubscriber $evaluationUpdateExpiredEventSubscriber;
    private Meeting $meeting;
    private Sheet $evaluatingSheet;
    private Sheet $evaluatedSheet;
    private User $evaluatingUser;

    protected function setUp()
    {
        $this->mailer = $this->prophesize(MailerInterface::class);
        $this->prepareHandler = $this->prophesize(PrepareHandler::class);
        $this->participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $this->eventUrlGeneratorInterface = $this->prophesize(EventUrlGeneratorInterface::class);
        $this->followupMailAccessRules = $this->prophesize(FollowUpMailAccessRules::class);
        $this->meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);

        $this->evaluationUpdateExpiredEventSubscriber = new EvaluationUpdateExpiredEventSubscriber(
            $this->mailer->reveal(),
            $this->prepareHandler->reveal(),
            $this->participantInfoGuesser->reveal(),
            $this->eventUrlGeneratorInterface->reveal(),
            $this->followupMailAccessRules->reveal(),
            $this->meetingRepository->reveal()
        );

        $this->evaluatingUser = UserFactory::create();
        $this->evaluatingSheet = SheetFactory::create(null, $this->evaluatingUser);
        $this->evaluatingSheet->setTitle('Acme Coop');
        $evaluatedUser = UserFactory::create('elon@bigboss.com', 2);
        $this->evaluatedSheet = SheetFactory::create(null, $evaluatedUser);
        $this->meeting = MeetingFactory::createMeeting($this->evaluatingSheet, $this->evaluatedSheet);
        $this->meeting->setParticipants($this->evaluatedSheet, [ParticipantFactory::create($this->evaluatedSheet, $evaluatedUser)]);
    }

    public function testOnMeetingEvaluationUpdateExpired()
    {
        $event = new MeetingEvaluationUpdateExpiredEvent(
            $this->meeting,
            $this->evaluatingSheet,
            $this->evaluatingUser,
            5,
            'fr'
        );

        $mail = $this->prophesize(MeetingFollowUpMail::class);
        $this->prepareHandler->handle(Argument::type(PrepareMeetingFollowUpView::class))->willReturn($mail->reveal());

        $accessRule = new ParticipantInfoAccessRule(4, 4, 4, false);
        $this->followupMailAccessRules
            ->createAccessRule($this->evaluatedSheet, $this->evaluatingSheet)
            ->willReturn($accessRule);
        $this->followupMailAccessRules
            ->canSendEmail($this->meeting, $this->evaluatedSheet, $accessRule, 5)->willReturn(true);

        $this->participantInfoGuesser
            ->guessParticipantInfos(Argument::type(Participant::class), 'fr')
            ->willReturn([
                Tag::PARTICIPANT_FIRSTNAME => 'John',
                Tag::PARTICIPANT_LASTNAME => 'Doe',
                Tag::PARTICIPANT_POSITION => 'CEO',
                Tag::PARTICIPANT_AVATAR => null,
                Tag::PARTICIPANT_PHONE => null,
            ]);

        $this->eventUrlGeneratorInterface->generateEventAbsoluteUrl(
            $this->evaluatedSheet->getEvent(),
            'event_catalog_complete_sheet',
            Argument::withEntry('sheet', $this->evaluatedSheet->getId())
        )->willReturn('https://domain.com/path/to/sheet');

        $this->evaluationUpdateExpiredEventSubscriber->onMeetingEvaluationUpdateExpired($event);

        $this->mailer->send($mail->reveal())->shouldHaveBeenCalled();

        $this->assertTrue($this->meeting->isFollowupSent($this->evaluatedSheet));
        $this->meetingRepository->set($this->meeting)->shouldHaveBeenCalled();
    }

    public function testCanNotSendEmail()
    {
        $event = new MeetingEvaluationUpdateExpiredEvent(
            $this->meeting,
            $this->evaluatingSheet,
            $this->evaluatingUser,
            2,
            'fr'
        );

        $mail = $this->prophesize(MeetingFollowUpMail::class);
        $this->prepareHandler->handle(Argument::type(PrepareMeetingFollowUpView::class))->willReturn($mail->reveal());

        $accessRule = new ParticipantInfoAccessRule(4, 4, 4, false);
        $this->followupMailAccessRules
            ->createAccessRule($this->evaluatedSheet, $this->evaluatingSheet)
            ->willReturn($accessRule);
        $this->followupMailAccessRules
            ->canSendEmail($this->meeting, $this->evaluatedSheet, $accessRule, 2)->willReturn(false);

        $this->evaluationUpdateExpiredEventSubscriber->onMeetingEvaluationUpdateExpired($event);

        $this->mailer->send()->shouldNotHaveBeenCalled();

        $this->assertFalse($this->meeting->isFollowupSent($this->evaluatedSheet));
        $this->meetingRepository->set()->shouldNotHaveBeenCalled();
    }

    public function testPrepareHandleIncorrectReturn()
    {
        $event = new MeetingEvaluationUpdateExpiredEvent(
            $this->meeting,
            $this->evaluatingSheet,
            $this->evaluatingUser,
            5,
            'fr'
        );

        $mail = $this->prophesize(MeetingFollowUpMail::class);
        $this->prepareHandler->handle(Argument::type(PrepareMeetingFollowUpView::class))->willReturn(null);

        $accessRule = new ParticipantInfoAccessRule(4, 4, 4, false);
        $this->followupMailAccessRules
            ->createAccessRule($this->evaluatedSheet, $this->evaluatingSheet)
            ->willReturn($accessRule);
        $this->followupMailAccessRules
            ->canSendEmail($this->meeting, $this->evaluatedSheet, $accessRule, 5)->willReturn(true);

        $this->participantInfoGuesser
            ->guessParticipantInfos(Argument::type(Participant::class), 'fr')
            ->willReturn([
                Tag::PARTICIPANT_FIRSTNAME => 'John',
                Tag::PARTICIPANT_LASTNAME => 'Doe',
                Tag::PARTICIPANT_POSITION => 'CEO',
                Tag::PARTICIPANT_AVATAR => null,
                Tag::PARTICIPANT_PHONE => null,
            ]);

        $this->eventUrlGeneratorInterface->generateEventAbsoluteUrl(
                $this->evaluatedSheet->getEvent(),
                'event_catalog_complete_sheet',
                Argument::withEntry('sheet', $this->evaluatedSheet->getId())
            )->willReturn('https://domain.com/path/to/sheet');

        $this->evaluationUpdateExpiredEventSubscriber->onMeetingEvaluationUpdateExpired($event);

        $this->mailer->send()->shouldNotHaveBeenCalled();

        $this->assertFalse($this->meeting->isFollowupSent($this->evaluatedSheet));
        $this->meetingRepository->set()->shouldNotHaveBeenCalled();
    }
}
