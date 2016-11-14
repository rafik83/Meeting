<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Application\Components\Meeting\RequestPermissionManager;
use Proximum\Vimeet\Application\Exception\MeetingRequest\IsNotAllowedToApproveMeetingRequestException;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class ApproveRequestHandler
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * @var MessageRepositoryInterface
     */
    private $messageRepository;

    /**
     * @var RequestPermissionManager
     */
    private $permissionManager;

    /**
     * @param RequestRepositoryInterface $requestRepository
     * @param MessageRepositoryInterface $messageRepository
     * @param RequestPermissionManager   $permissionManager
     * @param \DateTimeInterface         $datetime
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        MessageRepositoryInterface $messageRepository,
        RequestPermissionManager $permissionManager,
        \DateTimeInterface $datetime
    ) {
        $this->requestRepository = $requestRepository;
        $this->permissionManager = $permissionManager;
        $this->messageRepository = $messageRepository;
        $this->datetime          = $datetime;
    }

    /**
     * @param ApproveRequest $approveRequest
     *
     * @throws IsNotAllowedToApproveMeetingRequestException
     */
    public function handle(ApproveRequest $approveRequest)
    {
        if (!$this->permissionManager->isAllowedToApprove(
            $approveRequest->editor,
            $approveRequest->request,
            $approveRequest->sheet
        )) {
            throw new IsNotAllowedToApproveMeetingRequestException();
        }

        foreach ($approveRequest->request->getToParticipants() as $oldToParticipant) {
            if (!in_array($oldToParticipant, $approveRequest->participants)) {
                $approveRequest->request->removeToParticipant($oldToParticipant);
            }
        }

        foreach ($approveRequest->participants as $participant) {
            if (!$approveRequest->request->hasToParticipant($participant)) {
                $approveRequest->request->addToParticipant($participant);
            }
        }

        $this->requestRepository->set($approveRequest->request->approve($this->datetime));

        // Add message
        if ($approveRequest->description) {
            $this->messageRepository->add(new Message(
                $approveRequest->request,
                $approveRequest->request->getToSheet(),
                $approveRequest->description,
                $this->datetime
            ));
        }
    }
}
