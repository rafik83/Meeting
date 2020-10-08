<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Fairness Coop <contact@fairness.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class UpdateNetworkingChatViewedAtHandler
{
    /** @var ParticipantRepositoryInterface $participantRepository */
    private $participantRepository;

    public function __construct(ParticipantRepositoryInterface $participantRepository)
    {
        $this->participantRepository = $participantRepository;
    }

    public function handle(UpdateNetworkingChatViewedAt $command): void
    {
        $this->participantRepository->updateAllNetworkingChatViewedAt($command->user, $command->sheet->getEvent());
    }
}
