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
     * @var ValidateMobileProcessAccessChecker
     */
    private $validateMobileProcessAccessChecker;

    /**
     * PreUpdateHandler constructor.
     *
     * @param ValidateMobileProcessAccessChecker $validateMobileProcessAccessChecker
     * @param ParticipantInfoGuesser             $participantInfoGuesser
     */
    public function __construct(
        ValidateMobileProcessAccessChecker $validateMobileProcessAccessChecker,
        ParticipantInfoGuesser $participantInfoGuesser
    ) {
        $this->participantInfoGuesser             = $participantInfoGuesser;
        $this->validateMobileProcessAccessChecker = $validateMobileProcessAccessChecker;
    }

    /**
     * @param PreUpdate $update
     *
     * @return PreUpdateView
     * @throws \Exception
     */
    public function handle(PreUpdate $update): PreUpdateView
    {
        $allowToAccess = $this->validateMobileProcessAccessChecker
            ->allowToAccess($update->event, $update->user, $update->locale);

        $mobileTemplateObject = $update->templateData->getObjectByTag(Tag::PARTICIPANT_MOBILE);

        if (!$allowToAccess || $mobileTemplateObject === null) {
            return new PreUpdateView(null, self::MOBILE_VALIDATION_NOT_NEED);
        }

        $previousMobile = $this->participantInfoGuesser->guessParticipantMobile($update->participant, $update->locale);

        if (!isset($update->data[$mobileTemplateObject->getKey()]['telephone'])) {
            return new PreUpdateView(null, self::MOBILE_VALIDATION_NOT_NEED);
        };

        $currentMobile = $update->data[$mobileTemplateObject->getKey()]['telephone'];

        if ($currentMobile !== $previousMobile) {
            return new PreUpdateView($currentMobile, self::MOBILE_VALIDATION_NEEDED);
        }

        return new PreUpdateView($currentMobile, self::MOBILE_VALIDATION_NOT_NEED);
    }
}
