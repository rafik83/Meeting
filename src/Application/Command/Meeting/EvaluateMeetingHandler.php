<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;

class EvaluateMeetingHandler
{
    /** @var ContactRepositoryInterface */
    private $contactRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        ContactRepositoryInterface $contactRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->contactRepository = $contactRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(EvaluateMeeting $command): void
    {
        $event = $command->event;
        $participants = $command->meeting->getMetParticipants($command->sheet);

        foreach ($participants as $participant) {
            $this->addEvaluation($event, $command->user, $participant->getUser(), $command->evaluation);
        }
    }

    private function addEvaluation(Event $event, User $fromUser, User $toUser, int $evaluation): void
    {
        $contact = new Contact(
            $event,
            $fromUser,
            $toUser,
            $this->dateTime,
            false
        );

        $foundContact = $this->contactRepository->find($contact);

        if ($foundContact instanceof Contact) {
            $foundContact->setEvaluation($evaluation);
            $this->contactRepository->set($foundContact);

            return;
        }

        $contact->setEvaluation($evaluation);
        $this->contactRepository->add($contact);
    }
}
