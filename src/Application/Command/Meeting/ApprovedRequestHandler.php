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

class ApprovedRequestHandler
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
     * @param ApprovedRequest $approvedRequest
     */
    public function handle(ApprovedRequest $approvedRequest)
    {
        $toParticipants = [];

        if (!empty($approvedRequest->toParticipants)) {
            foreach ($approvedRequest->toParticipants as $toParticipant) {
                foreach ($approvedRequest->request->getTo()->getParticipants() as $sheetParticipant) {
                    if ($sheetParticipant->getId() === $toParticipant) {
                        $toParticipants[] = $sheetParticipant;
                    }
                }
            }
        }

        $approvedRequest->request->setToParticipants($toParticipants);
        $this->requestRepository->set($approvedRequest->request);
    }
}
