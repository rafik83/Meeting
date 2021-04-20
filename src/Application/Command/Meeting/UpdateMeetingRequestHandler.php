<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Application\Components\Meeting\RequestPermissionManager;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\MeetingRequest\ParticipateToRequestEvent;
use Proximum\Vimeet\Application\Event\MeetingRequest\UnParticipateToRequestEvent;
use Proximum\Vimeet\Application\Exception\MeetingRequest\IsNotAllowedToUpdateMeetingRequestException;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class UpdateMeetingRequestHandler
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var MessageRepositoryInterface
     */
    private $messageRepository;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @var RequestPermissionManager
     */
    private $requestPermissionManager;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    /**
     * EditRequestHandler constructor.
     *
     * @param RequestRepositoryInterface $requestRepository
     * @param MessageRepositoryInterface $messageRepository
     * @param RequestPermissionManager   $requestPermissionManager
     * @param \DateTimeInterface         $dateTime
     * @param DelayedEventDispatcher     $eventDispatcher
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        MessageRepositoryInterface $messageRepository,
        RequestPermissionManager $requestPermissionManager,
        \DateTimeInterface $dateTime,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->requestRepository        = $requestRepository;
        $this->messageRepository        = $messageRepository;
        $this->requestPermissionManager = $requestPermissionManager;
        $this->dateTime                 = $dateTime;
        $this->eventDispatcher          = $eventDispatcher;
    }

    /**
     * @param UpdateMeetingRequest $updateRequest
     *
     * @throws IsNotAllowedToUpdateMeetingRequestException
     */
    public function handle(UpdateMeetingRequest $updateRequest)
    {
        if (!$this->requestPermissionManager->isAllowedToEditSentOrApproved(
            $updateRequest->meetingRequest,
            $updateRequest->sheetEditor
        )) {
            throw new IsNotAllowedToUpdateMeetingRequestException('You are not allowed to update this request.');
        }

        if ($updateRequest->sheetEditor === $updateRequest->meetingRequest->getToSheet()) {
            $updateRequest->meetingRequest->setToPriority($updateRequest->isPriority);
        }

        if ($updateRequest->sheetEditor === $updateRequest->meetingRequest->getFromSheet()) {
            $updateRequest->meetingRequest->setFromPriority($updateRequest->isPriority);
        }

        $this->handleAddRemoveParticipant($updateRequest);

        // Add message
        if ($updateRequest->description) {
            $message = new Message(
                $updateRequest->meetingRequest,
                $updateRequest->sheetEditor,
                $updateRequest->description,
                $this->dateTime
            );

            $this->messageRepository->add($message);
            $updateRequest->meetingRequest->setHasMessage(true);
        }

        // Save request
        $this->requestRepository->set($updateRequest->meetingRequest);
    }

    /**
     * @param UpdateMeetingRequest $updateRequest
     */
    private function handleAddRemoveParticipant(UpdateMeetingRequest $updateRequest)
    {
        if ($updateRequest->sheetEditor === $updateRequest->meetingRequest->getFromSheet()) {
            // Remove removed participants
            foreach ($updateRequest->meetingRequest->getFromParticipants() as $participant) {
                if (!in_array($participant, $updateRequest->participants)) {
                    $updateRequest->meetingRequest->removeFromParticipant($participant);

                    if ($updateRequest->meetingRequest->isApproved()) {
                        $this->eventDispatcher->dispatch(
                            Events::REQUEST_UN_PARTICIPATE, new UnParticipateToRequestEvent($participant)
                        );
                    }
                }
            }

            // Add new participants
            foreach ($updateRequest->participants as $participant) {
                if (!$updateRequest->meetingRequest->hasFromParticipant($participant)) {
                    $updateRequest->meetingRequest->addFromParticipant($participant);

                    if ($updateRequest->meetingRequest->isApproved()) {
                        $this->eventDispatcher->dispatch(
                            Events::REQUEST_PARTICIPATE, new ParticipateToRequestEvent($participant)
                        );
                    }
                }
            }
        } elseif ($updateRequest->sheetEditor === $updateRequest->meetingRequest->getToSheet()) {
            // Remove removed participants
            foreach ($updateRequest->meetingRequest->getToParticipants() as $participant) {
                if (!in_array($participant, $updateRequest->participants)) {
                    $updateRequest->meetingRequest->removeToParticipant($participant);

                    if ($updateRequest->meetingRequest->isApproved()) {
                        $this->eventDispatcher->dispatch(
                            Events::REQUEST_UN_PARTICIPATE, new UnParticipateToRequestEvent($participant)
                        );
                    }
                }
            }

            // Add new participants
            foreach ($updateRequest->participants as $participant) {
                if (!$updateRequest->meetingRequest->hasToParticipant($participant)) {
                    $updateRequest->meetingRequest->addToParticipant($participant);

                    if ($updateRequest->meetingRequest->isApproved()) {
                        $this->eventDispatcher->dispatch(
                            Events::REQUEST_PARTICIPATE, new ParticipateToRequestEvent($participant)
                        );
                    }
                }
            }
        }
    }
}
