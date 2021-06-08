<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Application\Adapter\MessageBusInterface;
use Proximum\Vimeet\Application\Components\Worker\TimestampProvider;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingEvaluationUpdateExpiredEvent;
use Proximum\Vimeet\Application\Exception\Meeting\MeetingNotFoundException;
use Proximum\Vimeet\Domain\Exception\Meeting\MeetingException;
use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Model\Participant;
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

        if ($meeting === null) {
            throw new MeetingNotFoundException(
                sprintf(
                    'Meeting #%d not found when evalution reached timeout for user #%d',
                    $message->getMeetingId(),
                    $message->getFromUserId()
                )
            );
        }

        $fromUser = $this->userRepository->findOneById($message->getFromUserId());
        if ($fromUser === null) {
            throw new MeetingException(sprintf('User %d not found', $message->getFromUserId()));
        }

        $evaluatingSheet = $meeting->getSheetOfUser($fromUser);
        $evaluatedParticipants = $meeting->getMetParticipants($evaluatingSheet);

        // check if evaluation has been updated
        $latestEvaluatedAt = $this->contactRepository->findLatestEvaluatedAt(
            $meeting->getEvent()->getId(),
            $message->getFromUserId(),
            array_map(fn (Participant $p) => $p->getUser()->getId(), $evaluatedParticipants)
        );

        $now = $this->timestampProvider->getTimestamp();
        if ($latestEvaluatedAt->getTimestamp() + EvaluationTimeoutMessage::WAIT_DELAY > $now) {
            $remainingDelay = max(0, $latestEvaluatedAt->getTimestamp() + EvaluationTimeoutMessage::WAIT_DELAY - $now);
            $this->messageBus->dispatchDelayed($message, $remainingDelay);

            return;
        }

        // retrieve contact to get evaluation
        $contact = $this->contactRepository->find(new Contact(
            $meeting->getEvent(),
            $fromUser,
            // any user met can be used, since evaluation is the same for all met users
            $evaluatedParticipants[0]->getUser(),
            new \DateTime(),
            Contact::ORIGIN_MEETING
        ));

        if ($contact === null) {
            if ($this->logger) {
                $this->logger->warning(sprintf(
                    'Contact not found for users %d/%d when dispatching MeetingEvaluationUpdateExpiredEvent',
                    $message->getFromUserId(),
                    $evaluatedParticipants[0]->getUser()->getId()
                ));
            }

            return;
        }

        $this->eventDispatcher->dispatch(Events::MEETING_EVALUATION_UPDATE_EXPIRED, new MeetingEvaluationUpdateExpiredEvent(
            $meeting,
            $evaluatingSheet,
            $fromUser,
            $contact->getEvaluation()
        ));
    }
}
