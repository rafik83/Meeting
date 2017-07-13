<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Profile;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Security\ValidateMobileProcessAccessChecker;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Session\UpdateParticipantSessionManager;

class PreUpdateHandler
{
    const MOBILE_VALIDATION_NEEDED   = 'mobile_validation_needed';
    const MOBILE_VALIDATION_NOT_NEED = 'mobile_validation_not_need';
    const MOBILE_NUMBER_TO_VALIDATE  = 'mobile_number_to_validate';

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * @var UpdateParticipantSessionManager
     */
    private $updateParticipantSessionManager;

    /**
     * @var ValidateMobileProcessAccessChecker
     */
    private $validateMobileProcessAccessChecker;

    /**
     * PreUpdateHandler constructor.
     *
     * @param ValidateMobileProcessAccessChecker $validateMobileProcessAccessChecker
     * @param ParticipantInfoGuesser             $participantInfoGuesser
     * @param UpdateParticipantSessionManager    $updateParticipantSessionManager
     */
    public function __construct(
        ValidateMobileProcessAccessChecker $validateMobileProcessAccessChecker,
        ParticipantInfoGuesser $participantInfoGuesser,
        UpdateParticipantSessionManager $updateParticipantSessionManager
    ) {
        $this->participantInfoGuesser             = $participantInfoGuesser;
        $this->updateParticipantSessionManager    = $updateParticipantSessionManager;
        $this->validateMobileProcessAccessChecker = $validateMobileProcessAccessChecker;
    }

    /**
     * @param PreUpdate $update
     *
     * @return string
     * @throws \Exception
     */
    public function handle(PreUpdate $update): string
    {
        $allowToAccess = $this->validateMobileProcessAccessChecker
            ->allowToAccess($update->event, $update->user, $update->locale);

        $mobileTemplateObject = $update->templateData->getObjectByTag(Tag::PARTICIPANT_MOBILE);

        if (!$allowToAccess || $mobileTemplateObject === null) {
            return self::MOBILE_VALIDATION_NOT_NEED;
        }

        $previousMobile = $this->participantInfoGuesser->guessParticipantMobile($update->participant, $update->locale);

        if (!isset($update->data[$mobileTemplateObject->getKey()]['telephone'])) {
            throw new \Exception();
        };

        $currentMobile = $update->data[$mobileTemplateObject->getKey()]['telephone'];

        if ($currentMobile !== $previousMobile) {
            $this->updateParticipantSessionManager->set(
                $update->participant->getSheet(),
                $update->participant,
                $currentMobile
            );

            return self::MOBILE_VALIDATION_NEEDED;
        }

        return self::MOBILE_VALIDATION_NOT_NEED;
    }
}
