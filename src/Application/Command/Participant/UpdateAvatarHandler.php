<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class UpdateAvatarHandler
{
    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var Synchronizer
     */
    private $accountSynchronizer;

    /**
     * @param ParticipantRepositoryInterface $participantRepository
     * @param Synchronizer                   $accountSynchronizer
     */
    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        Synchronizer $accountSynchronizer
    ) {
        $this->participantRepository = $participantRepository;
        $this->accountSynchronizer   = $accountSynchronizer;
    }

    /**
     * @param UpdateAvatar $updateAvatar
     */
    public function handle(UpdateAvatar $updateAvatar)
    {
        $participant = $updateAvatar->participant;
        $participant->setData($updateAvatar->templateData->getData());

        $this->participantRepository->set($participant);

        if ($participant->getUser() === $updateAvatar->user) {
            $this->accountSynchronizer->set($updateAvatar->templateData, $participant->getUser());
        }
    }
}
