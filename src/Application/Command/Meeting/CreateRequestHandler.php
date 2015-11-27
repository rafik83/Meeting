<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class CreateRequestHandler
{
    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @param ParticipantRepositoryInterface $participantRepository
     * @param RequestRepositoryInterface $requestRepository
     */
    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        RequestRepositoryInterface $requestRepository
    ) {
        $this->participantRepository = $participantRepository;
        $this->requestRepository     = $requestRepository;
    }

    public function handle(CreateRequest $createRequest)
    {
        $fromParticipants = [];
        if (!empty($createRequest->fromParticipants)) {
            foreach ($createRequest->fromParticipants as $fromParticipant) {
                foreach ($createRequest->from->getParticipants() as $sheetParticipant) {
                    if ($sheetParticipant->getId() === $fromParticipant) {
                        $fromParticipants[] = $sheetParticipant;
                    }
                }
            }
        }

        $request = new Request($createRequest->from, $fromParticipants, $createRequest->to, $createRequest->description, $createRequest->createdAt);
        $this->requestRepository->add($request);
    }
}
