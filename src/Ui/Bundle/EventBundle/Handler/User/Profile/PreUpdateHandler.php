<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Profile;

use League\Tactician\CommandBus;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewByUserQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class PreUpdateHandler
{
    const MOBILE_VALIDATION_NEEDED   = 'mobile_validation_needed';
    const MOBILE_VALIDATION_NOT_NEED = 'mobile_validation_not_need';

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * PreUpdateHandler constructor.
     *
     * @param CommandBus             $commandBus
     * @param ParticipantInfoGuesser $participantInfoGuesser
     */
    public function __construct(CommandBus $commandBus, ParticipantInfoGuesser $participantInfoGuesser)
    {
        $this->commandBus             = $commandBus;
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    /**
     * @param PreUpdate $update
     *
     * @return string
     * @throws \Exception
     */
    public function handle(PreUpdate $update): string
    {
        $tipTranslationViews = $this->commandBus->handle(
            new TipTranslationViewByUserQuery(
                $update->event,
                $update->user,
                TipTranslationViewQueryHandler::CONTEXT_CONFIRMATION_PHONE,
                $update->locale
            )
        );

        $mobileTemplateObject = $update->templateData->getObjectByTag(Tag::PARTICIPANT_MOBILE);

        if ($tipTranslationViews === null || $mobileTemplateObject === null) {
            return self::MOBILE_VALIDATION_NOT_NEED;
        }

        $previousMobile = $this->participantInfoGuesser->guessParticipantMobile($update->participant, $update->locale);

        if (!isset($update->data[$mobileTemplateObject->getKey()]['telephone'])) {
            throw new \Exception();
        };

        $currentMobile = $update->data[$mobileTemplateObject->getKey()]['telephone'];

        if ($currentMobile !== $previousMobile) {
            return self::MOBILE_VALIDATION_NEEDED;
        }

        return self::MOBILE_VALIDATION_NOT_NEED;
    }
}
