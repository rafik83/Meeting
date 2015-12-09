<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class ApproveRequestHandler
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @param RequestRepositoryInterface $requestRepository
     */
    public function __construct(RequestRepositoryInterface $requestRepository)
    {
        $this->requestRepository = $requestRepository;
    }

    /**
     * @param ApproveRequest $approveRequest
     */
    public function handle(ApproveRequest $approveRequest)
    {
        foreach ($approveRequest->toParticipants as $participant) {
            $approveRequest->request->addToParticipant($participant);
        }

        $this->requestRepository->set($approveRequest->request);
    }
}
