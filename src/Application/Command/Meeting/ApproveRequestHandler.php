<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

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
     * @param RequestRepositoryInterface $requestRepository
     * @param MessageRepositoryInterface $messageRepository
     * @param \DateTimeInterface         $datetime
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        MessageRepositoryInterface $messageRepository,
        \DateTimeInterface $datetime
    ) {
        $this->requestRepository = $requestRepository;
        $this->datetime          = $datetime;
        $this->messageRepository = $messageRepository;
    }

    /**
     * @param ApproveRequest $approveRequest
     */
    public function handle(ApproveRequest $approveRequest)
    {
        foreach ($approveRequest->participants as $participant) {
            $approveRequest->request->addToParticipant($participant);
        }

        // Add message
        if ($approveRequest->description) {
            $this->messageRepository->add(new Message(
                $approveRequest->request,
                $approveRequest->request->getToSheet(),
                $approveRequest->description,
                $this->datetime
            ));
        }

        $this->requestRepository->set($approveRequest->request->approve($this->datetime));
    }
}
