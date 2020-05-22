<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Contact;
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
            $contact = new Contact(
                $event,
                $command->user,
                $participant->getUser(),
                $this->dateTime,
                false
            );

            $foundContact = $this->contactRepository->find($contact);

            if ($foundContact instanceof Contact) {
                $foundContact->setEvaluation($command->evaluation);
                $this->contactRepository->set($foundContact);

                continue;
            }

            $contact->setEvaluation($command->evaluation);
            $this->contactRepository->add($contact);
        }
    }
}
