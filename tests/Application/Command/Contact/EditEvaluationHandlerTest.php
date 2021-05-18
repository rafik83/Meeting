<?php

namespace Proximum\Vimeet\Tests\Application\Command\Contact;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\MessageBusInterface;
use Proximum\Vimeet\Application\Command\Contact\EditEvaluation;
use Proximum\Vimeet\Application\Command\Contact\EditEvaluationHandler;
use Proximum\Vimeet\Application\Command\Meeting\EvaluationTimeoutMessage;
use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class EditEvaluationHandlerTest extends TestCase
{
    private \DateTimeInterface $dateTime;
    private Sheet $sheet;
    private ObjectProphecy $contactRepository;
    private ObjectProphecy $meetingRepository;
    private ObjectProphecy $messageBus;

    protected function setUp()
    {
        $this->sheet = SheetFactory::create();
        $this->dateTime = \DateTime::createFromFormat('!Y-m-d H:i', '2021-01-20 13:45');

        // prophecies dependencies
        $this->contactRepository = $this->prophesize(ContactRepositoryInterface::class);
        $this->meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $this->messageBus = $this->prophesize(MessageBusInterface::class);
    }

    public function testHandleWhenEvaluationIsUpdated(): void
    {
        // prepare data
        $contact = $this->prophesize(Contact::class);
        $contact->hasEvaluation()->shouldBeCalled()->willReturn(true);
        $contact->setEvaluation(2, $this->dateTime)->shouldBeCalled();
        $this->contactRepository->set($contact)->shouldBeCalled();

        // run tests
        $command = new EditEvaluation($contact->reveal(), 2, $this->sheet, $this->dateTime);

        $editCommentHandler = new EditEvaluationHandler(
            $this->contactRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->messageBus->reveal(),
            null
        );
        $editCommentHandler->handle($command);

        $this->messageBus->dispatchDelayed()->shouldNotHaveBeencalled();
    }

    public function testHandleWhenEvaluationIsCreated(): void
    {
        // prepare data
        $contact = $this->prophesize(Contact::class);
        $contact->hasEvaluation()->shouldBeCalled()->willReturn(false);
        $contact->setEvaluation(2, $this->dateTime)->shouldBeCalled();
        $contact->getEvent()->willReturn($event = EventFactory::createEvent('Fake event'));
        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(512);
        $contact->getUser()->willReturn($user->reveal());
        $contactedUser = $this->prophesize(User::class);
        $contactedUser->getId()->willReturn(215);
        $contact->getContact()->willReturn($contactedUser->reveal());
        $this->contactRepository->set($contact)->shouldBeCalled();

        $meeting = $this->prophesize(Meeting::class);
        $meeting->getId()->willReturn(26001);
        $meeting->hasSheet($this->sheet)->willReturn(true);
        $this->meetingRepository
            ->findByUsers($event, $user->reveal(), $contactedUser->reveal())
            ->shouldBeCalled()
            ->willReturn([$meeting->reveal()]);

        // run tests
        $command = new EditEvaluation($contact->reveal(), 2, $this->sheet, $this->dateTime);

        $editCommentHandler = new EditEvaluationHandler(
            $this->contactRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->messageBus->reveal(),
            null
        );
        $editCommentHandler->handle($command);

        $expectedMessage = new EvaluationTimeoutMessage($meeting->reveal(), $user->reveal(), [$contact->reveal()]);
        $this->messageBus->dispatchDelayed($expectedMessage, EvaluationTimeoutMessage::WAIT_DELAY)->shouldHaveBeencalled();
    }

    public function testHandleMeetingNotFound(): void
    {
        // prepare data
        $contact = $this->prophesize(Contact::class);
        $contact->hasEvaluation()->shouldBeCalled()->willReturn(false);
        $contact->setEvaluation(2, $this->dateTime)->shouldBeCalled();
        $contact->getEvent()->willReturn($event = EventFactory::createEvent('Fake event'));
        $user = $this->prophesize(User::class);
        $contact->getUser()->willReturn($user->reveal());
        $contactedUser = $this->prophesize(User::class);
        $contact->getContact()->willReturn($contactedUser->reveal());
        $this->contactRepository->set($contact)->shouldBeCalled();

        $this->meetingRepository
            ->findByUsers($event, $user->reveal(), $contactedUser->reveal())
            ->shouldBeCalled()
            ->willReturn([]);

        // run tests
        $command = new EditEvaluation($contact->reveal(), 2, $this->sheet, $this->dateTime);

        $editCommentHandler = new EditEvaluationHandler(
            $this->contactRepository->reveal(),
            $this->meetingRepository->reveal(),
            $this->messageBus->reveal(),
            null
        );
        $editCommentHandler->handle($command);

        $this->messageBus->dispatchDelayed()->shouldNotHaveBeencalled();
    }
}
