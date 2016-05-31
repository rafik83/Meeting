<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class UpdateProfileHandler
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
     * @param UpdateProfile $updateProfile
     */
    public function handle(UpdateProfile $updateProfile)
    {
        $participant     = $updateProfile->participant;
        $participantData = $updateProfile->participant->getData();
        $templateData    = $updateProfile->templateData;

        foreach ($updateProfile->data as $key => $value) {
            if ($templateData->getObject($key)->hasTag(Tag::PARTICIPANT_DATA)) {
                $participantData = array_merge($participantData, [$key => $value]);

                // Set the data on the TemplateData to use it with the accountSynchronizer
                $templateData->getObject($key)->setData($value);
            }
        }

        $updateProfile->participant->setData($participantData);

        $this->participantRepository->set($participant);

        if ($participant->getUser() === $updateProfile->user) {
            $this->accountSynchronizer->set($templateData, $updateProfile->participant->getUser());
        }
    }
}
