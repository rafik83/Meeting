<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Application\Adapter\MessageBusInterface;
use Proximum\Vimeet\Application\Components\Worker\TimestampProvider;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingEvaluationUpdateExpiredEvent;
use Proximum\Vimeet\Domain\Exception\Meeting\MeetingException;
use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EvaluationTimeoutHandler
{
    private ContactRepositoryInterface $contactRepository;
    private MeetingRepositoryInterface $meetingRepository;
    private UserRepositoryInterface $userRepository;
    private MessageBusInterface $messageBus;
    private EventDispatcherInterface $eventDispatcher;
    private TimestampProvider $timestampProvider;
    private ?LoggerInterface $logger;

    public function __construct(
        ContactRepositoryInterface $contactRepository,
        MeetingRepositoryInterface $meetingRepository,
        UserRepositoryInterface $userRepository,
        MessageBusInterface $messageBus,
        EventDispatcherInterface $eventDispatcher,
        TimestampProvider $timestampProvider,
        ?LoggerInterface $logger
    ) {
        $this->contactRepository = $contactRepository;
        $this->meetingRepository = $meetingRepository;
        $this->userRepository = $userRepository;
        $this->messageBus = $messageBus;
        $this->eventDispatcher = $eventDispatcher;
        $this->timestampProvider = $timestampProvider;
        $this->logger = $logger;
    }

    public function handle(EvaluationTimeoutMessage $message)
    {
        $meeting = $this->meetingRepository->findById($message->getMeetingId());

        // check if evaluation has been updated
        $latestEvaluatedAt = $this->contactRepository->findLatestEvaluatedAt(
            $meeting->getEvent()->getId(),
            $message->getFromUserId(),
            $message->getContactIds()
        );

        $now = $this->timestampProvider->getTimestamp();
        if ($latestEvaluatedAt->getTimestamp() + EvaluationTimeoutMessage::WAIT_DELAY > $now) {
            $remainingDelay = max(0, $latestEvaluatedAt->getTimestamp() + EvaluationTimeoutMessage::WAIT_DELAY - $now);
            $this->messageBus->dispatchDelayed($message, $remainingDelay);

            return;
        }

        $fromUser = $this->userRepository->findOneById($message->getFromUserId());
        if ($fromUser === null) {
            throw new MeetingException(sprintf('User %d not found', $message->getFromUserId()));
        }

        $evaluatingSheet = $meeting->getSheetOfUser($fromUser);

        foreach ($message->getContactIds() as $contactId) {
            $contactUser = $this->userRepository->findOneById($contactId);

            if ($contactUser === null) {
                if ($this->logger) {
                    $this->logger->warning(sprintf('User %d not found when dispatching MeetingEvaluationUpdateExpiredEvent', $contactId));
                }
                continue;
            }

            $contact = $this->contactRepository->find(new Contact(
                $meeting->getEvent(),
                $fromUser,
                $contactUser,
                new \DateTime(),
                Contact::ORIGIN_MEETING
            ));

            if ($contact === null) {
                if ($this->logger) {
                    $this->logger->warning(sprintf(
                        'Contact not found for users %d/%d when dispatching MeetingEvaluationUpdateExpiredEvent',
                        $message->getFromUserId(),
                        $contactId
                    ));
                }
                continue;
            }

            $this->eventDispatcher->dispatch(Events::MEETING_EVALUATION_UPDATE_EXPIRED, new MeetingEvaluationUpdateExpiredEvent(
                $meeting,
                $contactUser,
                $evaluatingSheet,
                $contact->getEvaluation()
            ));
        }
    }
}
