<?php

namespace Proximum\Vimeet\Application\Command\Contact;

use Proximum\Vimeet\Application\Adapter\MessageBusInterface;
use Proximum\Vimeet\Application\Command\Meeting\EvaluationTimeoutMessage;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Psr\Log\LoggerInterface;

class EditEvaluationHandler
{
    private ContactRepositoryInterface $contactRepository;
    private MeetingRepositoryInterface $meetingRepository;
    private MessageBusInterface $messageBus;

    public function __construct(
        ContactRepositoryInterface $contactRepository,
        MeetingRepositoryInterface $meetingRepository,
        MessageBusInterface $messageBus,
        ?LoggerInterface $logger
    ) {
        $this->contactRepository = $contactRepository;
        $this->meetingRepository = $meetingRepository;
        $this->messageBus = $messageBus;
        $this->logger = $logger;
    }

    public function handle(EditEvaluation $command): void
    {
        $sendMessage = !$command->contact->hasEvaluation();
        $command->contact->setEvaluation($command->evaluation, $command->evaluatedAt);
        $this->contactRepository->set($command->contact);

        // Send message only once
        if ($sendMessage) {
            $meeting = $this->meetingRepository->findOneByUsers(
                $command->contact->getEvent(),
                $command->contact->getUser(),
                $command->contact->getContact()
            );

            if ($meeting === null) {
                // Log error if meeting is not found
                if ($this->logger) {
                    $this->logger->error(sprintf(
                        '[EditEvaluationHandler] Event %d, meeting not found for contact %d/%d',
                        $command->contact->getEvent()->getId(),
                        $command->contact->getUser()->getId(),
                        $command->contact->getContact()->getId()
                    ));
                }

                return;
            }

            $message = new EvaluationTimeoutMessage($meeting, $command->contact->getUser(), [$command->contact]);
            $this->messageBus->dispatchDelayed($message, EvaluationTimeoutMessage::WAIT_DELAY);
        }
    }
}
