<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Registration;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class StepManager
{
    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * StepManager constructor.
     *
     * @param TemplateDataFactory            $templateDataFactory
     * @param ParticipantRepositoryInterface $participantRepository
     */
    public function __construct(
        TemplateDataFactory $templateDataFactory,
        ParticipantRepositoryInterface $participantRepository
    ) {
        $this->templateDataFactory   = $templateDataFactory;
        $this->participantRepository = $participantRepository;
    }

    /**
     * @param Participant $participant
     * @param int         $step
     */
    public function updateCurrentStep(Participant $participant, $step)
    {
        $participant->setRegistrationStep($step);

        if (true === $this->isRegistrationComplete($participant)) {
            $participant->setRegistrationComplete(true);
        }

        $this->participantRepository->set($participant);
    }

    /**
     * @param Participant $participant
     *
     * @return bool
     */
    public function isRegistrationComplete(Participant $participant)
    {
        $registrationTemplate = $this->templateDataFactory->createRegistrationFromParticipant(
            $participant,
            $participant->getLocale()
        );

        $objects = $registrationTemplate->getObjects();

        foreach ($objects as $object) {
            if (true === $object->getRequired() && empty($object->getData())) {
                return false;
            }
        }

        return true;
    }
}
