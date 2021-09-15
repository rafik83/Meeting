<?php

namespace Proximum\Vimeet\Tests\Application\Command\Meeting;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\MessageBusInterface;
use Proximum\Vimeet\Application\Command\Meeting\EvaluateMeeting;
use Proximum\Vimeet\Application\Command\Meeting\EvaluateMeetingHandler;
use Proximum\Vimeet\Application\Command\Meeting\EvaluationTimeoutMessage;
use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;

class EvaluateMeetingHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $contactRepository = $this->prophesize(ContactRepositoryInterface::class);
        $date = new \DateTime();
        $messageBus = $this->prophesize(MessageBusInterface::class);
        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);
        $meeting = $this->prophesize(Meeting::class);
        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(118);

        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);

        $userMet1 = $this->prophesize(User::class);
        $userMet1->getId()->willReturn(1);
        $userMet2 = $this->prophesize(User::class);
        $userMet2->getId()->willReturn(2);

        $meeting->getId()->willReturn(486);
        $meeting->getMetParticipants($sheet->reveal())
            ->shouldBeCalled()
            ->willReturn([$participant1->reveal(), $participant2->reveal()])
        ;

        $participant1->getUser()->shouldBeCalled()->willReturn($userMet1->reveal());
        $participant2->getUser()->shouldBeCalled()->willReturn($userMet2->reveal());

        $contact1 = new Contact(
            $event->reveal(),
            $user->reveal(),
            $userMet1->reveal(),
            $date,
            Contact::ORIGIN_MEETING
        );

        $contact2 = new Contact(
            $event->reveal(),
            $user->reveal(),
            $userMet2->reveal(),
            $date,
            Contact::ORIGIN_MEETING
        );

        $expectedContact1 = new Contact(
            $event->reveal(),
            $user->reveal(),
            $userMet1->reveal(),
            $date,
            Contact::ORIGIN_MEETING
        );
        $expectedContact1->setEvaluation(4, $date);

        $expectedContact2 = new Contact(
            $event->reveal(),
            $user->reveal(),
            $userMet2->reveal(),
            $date,
            Contact::ORIGIN_MEETING
        );
        $expectedContact2->setEvaluation(4, $date);

        $contactRepository
            ->find($contact1)
            ->shouldBeCalled()
            ->willReturn($contact1)
        ;

        $contactRepository
            ->find($contact2)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $contactRepository->set($expectedContact1)->shouldBeCalled();
        $contactRepository->add($expectedContact2)->shouldBeCalled();

        $expectedMessage = new EvaluationTimeoutMessage(
            $meeting->reveal(),
            $user->reveal()
        );
        $messageBus->dispatchDelayed($expectedMessage, EvaluationTimeoutMessage::WAIT_DELAY)->shouldBeCalled();

        $handler = new EvaluateMeetingHandler(
            $contactRepository->reveal(),
            $date,
            $messageBus->reveal()
        );

        $command = new EvaluateMeeting(
            $event->reveal(),
            $sheet->reveal(),
            $meeting->reveal(),
            $user->reveal()
        );
        $command->evaluation = 4;

        $handler->handle($command);
    }
}
