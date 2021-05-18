<?php

namespace Proximum\Vimeet\Tests\Application\Components\Transactional\Mail;

use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Prepare\PrepareMeetingFollowUpMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstitutionHandler;
use Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution\SubstitutionResult;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareMeetingFollowUpView;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQuery;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQueryHandler;
use Proximum\Vimeet\Application\View\Meeting\FollowUpParticipantListView;
use Proximum\Vimeet\Application\View\Meeting\FollowUpParticipantView;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transactional\Mail\Message;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Transactional\Mail\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Transactional\Mail\Constant;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\EventSender;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Meeting\MeetingFollowUpCustomizedMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Meeting\MeetingFollowUpMail;

class PrepareMeetingFollowUpMailTest extends TestCase
{
    private PrepareMeetingFollowUpMail $prepareMeetingFollowUpMail;
    private ObjectProphecy $messageRepositoryInterface;
    private ObjectProphecy $substitutionHandler;
    private ObjectProphecy $eventSenderGuesser;
    private ObjectProphecy $participantMailViewQueryHandler;
    private ObjectProphecy $event;
    private ObjectProphecy $evaluatedUser;
    private ParticipantInfoView $participantInfoView;
    private FollowUpParticipantListView $metParticipants;
    private PrepareMeetingFollowUpView $prepareMail;

    public function setUp()
    {
        $this->messageRepositoryInterface = $this->prophesize(MessageRepositoryInterface::class);

        $this->substitutionHandler = $this->prophesize(SubstitutionHandler::class);
        $this->eventSenderGuesser = $this->prophesize(EventSender::class);
        $this->participantMailViewQueryHandler = $this->prophesize(ParticipantMailViewQueryHandler::class);

        $this->event = $this->prophesize(Event::class);
        $this->eventSenderGuesser->generate($this->event->reveal())->willReturn('sender@example.net');

        // user from sheet that has been evaluated
        $this->evaluatedUser = $this->prophesize(User::class);
        $this->evaluatedUser->getEmail()->willReturn('john@doe.com');
        $this->evaluatedSheet = $this->prophesize(Sheet::class);
        $this->evaluatedSheet->getId()->willReturn(8);
        $this->evaluatedSheet->getTitle()->willReturn('Fairness');

        $this->participantInfoView = new ParticipantInfoView('John', 'Doe');
        $this->participantMailViewQueryHandler
            ->handle(Argument::type(ParticipantMailViewQuery::class))
            ->willReturn($this->participantInfoView);

        $this->metParticipants = new FollowUpParticipantListView(
            [
                new FollowUpParticipantView('Alice', 'Dupont', 'CTO', 4, null, 'alice@example.net', '+33111224466')
            ]
        );

        $this->prepareMail = new PrepareMeetingFollowUpView(
            $this->event->reveal(),
            $this->evaluatedUser->reveal(),
            'fr',
            $this->evaluatedSheet->reveal(),
            'Acme Corp',
            5,
            $this->metParticipants
        );

        $this->prepareMeetingFollowUpMail = new PrepareMeetingFollowUpMail(
            $this->messageRepositoryInterface->reveal(),
            $this->substitutionHandler->reveal(),
            $this->eventSenderGuesser->reveal(),
            $this->participantMailViewQueryHandler->reveal()
        );
    }

    public function testPrepareStandardMail()
    {
        // Given there is no custom message
        $this->messageRepositoryInterface
            ->getOneByEventAndType($this->event->reveal(), Constant::TRANSACTIONAL_MAIL_KEY_MEETING_FOLLOW_UP)
            ->willReturn(null);

        // When prepareMeetingFollowUpMail is called
        $preparedMail = $this->prepareMeetingFollowUpMail->prepare($this->prepareMail);

        // Then I should get this result
        $expectedMail = new MeetingFollowUpMail(
            $this->event->reveal(),
            'sender@example.net',
            'john@doe.com',
            'fr',
            $this->participantInfoView,
            8,
            'Fairness',
            'Acme Corp',
            5,
            $this->metParticipants
        );

        $this->assertEquals($expectedMail, $preparedMail);
    }

    public function testPrepareCustomMail()
    {
        // Given there is a custom message
        $customMessage = new Message(
            $this->event->reveal(),
            Constant::TRANSACTIONAL_MAIL_KEY_MEETING_FOLLOW_UP,
            DateTime::createFromFormat('!Y-m-d H:i', '2020-11-09 02:00')
        );
        $this->messageRepositoryInterface
            ->getOneByEventAndType($this->event->reveal(), Constant::TRANSACTIONAL_MAIL_KEY_MEETING_FOLLOW_UP)
            ->willReturn($customMessage);

        // And substitution handler return something
        $substitution = new SubstitutionResult('subjet containing placeholders', 'body containing placeholders');
        $this->substitutionHandler->handle($this->prepareMail, $customMessage)->willReturn($substitution);

        // When prepareMeetingFollowUpMail is called
        $preparedMail = $this->prepareMeetingFollowUpMail->prepare($this->prepareMail);

        // Then I should get this result
        $expectedMail = new MeetingFollowUpCustomizedMail(
            $this->event->reveal(),
            'sender@example.net',
            'john@doe.com',
            'fr',
            'subjet containing placeholders',
            'body containing placeholders'
        );

        $this->assertEquals($expectedMail, $preparedMail);
    }

    public function testPrepareDisabledCustomMail()
    {
        // Given there is a custom message
        $customMessage = new Message(
            $this->event->reveal(),
            Constant::TRANSACTIONAL_MAIL_KEY_MEETING_FOLLOW_UP,
            DateTime::createFromFormat('!Y-m-d H:i', '2020-11-09 02:00'),
            false
        );
        $this->messageRepositoryInterface
            ->getOneByEventAndType($this->event->reveal(), Constant::TRANSACTIONAL_MAIL_KEY_MEETING_FOLLOW_UP)
            ->willReturn($customMessage);

        // When prepareMeetingFollowUpMail is called
        $preparedMail = $this->prepareMeetingFollowUpMail->prepare($this->prepareMail);

        // Then I should get a null result
        $this->assertNull($preparedMail);
    }
}
