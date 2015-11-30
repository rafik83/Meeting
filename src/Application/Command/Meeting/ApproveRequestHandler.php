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
        $toParticipants = [];

        if (!empty($approveRequest->toParticipants)) {
            foreach ($approveRequest->toParticipants as $toParticipant) {
                foreach ($approveRequest->request->getTo()->getParticipants() as $sheetParticipant) {
                    if ($sheetParticipant->getId() === $toParticipant) {
                        $toParticipants[] = $sheetParticipant;
                    }
                }
            }
        }

        $approveRequest->request->setToParticipants($toParticipants);
        $this->requestRepository->set($approveRequest->request);
    }
}
