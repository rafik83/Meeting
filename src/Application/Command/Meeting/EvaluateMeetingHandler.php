<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\MessageBusInterface;
use Proximum\Vimeet\Application\Command\Meeting\EvaluationTimeoutMessage;
use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;

class EvaluateMeetingHandler
{
    private ContactRepositoryInterface $contactRepository;
    private DateTimeInterface $dateTime;
    private MessageBusInterface $messageBus;

    public function __construct(
        ContactRepositoryInterface $contactRepository,
        DateTimeInterface $dateTime,
        MessageBusInterface $messageBus
    ) {
        $this->contactRepository = $contactRepository;
        $this->dateTime = $dateTime;
        $this->messageBus = $messageBus;
    }

    public function handle(EvaluateMeeting $command): void
    {
        if (null === $command->evaluation) {
            return;
        }

        $event = $command->event;
        $participants = $command->meeting->getMetParticipants($command->sheet);

        $contacts = [];
        foreach ($participants as $participant) {
            $contacts[] = $this->addEvaluation($event, $command->user, $participant->getUser(), $command->evaluation);
        }

        if (!count($contacts)) {
            return;
        }

        $message = new EvaluationTimeoutMessage($command->meeting, $command->user, $contacts);
        $this->messageBus->dispatchDelayed($message, EvaluationTimeoutMessage::WAIT_DELAY);
    }

    private function addEvaluation(Event $event, User $fromUser, User $toUser, int $evaluation): Contact
    {
        $contact = new Contact(
            $event,
            $fromUser,
            $toUser,
            $this->dateTime,
            Contact::ORIGIN_MEETING
        );

        $foundContact = $this->contactRepository->find($contact);

        if ($foundContact instanceof Contact) {
            $foundContact->setEvaluation($evaluation, $this->dateTime);
            $this->contactRepository->set($foundContact);

            return $foundContact;
        }

        $contact->setEvaluation($evaluation, $this->dateTime);
        $this->contactRepository->add($contact);

        return $contact;
    }
}
