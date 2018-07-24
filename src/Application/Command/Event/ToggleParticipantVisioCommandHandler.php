<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class ToggleParticipantVisioCommandHandler
{
    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    public function __construct(ParticipantRepositoryInterface $participantRepository)
    {
        $this->participantRepository = $participantRepository;
    }

    public function handle(ToggleParticipantVisioCommand $command): void
    {
        $this->participantRepository->toggleParticipantVisioForEvent($command->event, $command->visio);
    }
}
