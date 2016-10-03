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
use Proximum\Vimeet\Domain\Model\Sheet;
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
     */
    public function resetRegistrationStep(Participant $participant)
    {
        $participant->setRegistrationComplete($this->isRegistrationComplete($participant));
        $participant->setRegistrationStep(0);

        $this->participantRepository->set($participant);
    }

    /**
     * @param Participant $participant
     *
     * @return bool
     */
    private function isRegistrationComplete(Participant $participant)
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

    /**
     * @param Sheet       $sheet
     * @param Participant $participant
     *
     * @return array
     */
    public function getRedirectStep(Sheet $sheet, Participant $participant)
    {
        if (true === $this->isRegistrationComplete($participant)) {
            return ['redirect' => false];
        }

        $registrationTemplate = $this->templateDataFactory->createRegistrationFromParticipant(
            $participant,
            $participant->getLocale()
        );

        if ($sheet->getOwner() !== $participant->getUser()) {
            return [
                'redirect'   => true,
                'route'      => 'event_account_participant_profile',
                'parameters' => [
                    'sheet'       => $sheet->getId(),
                    'participant' => $participant->getId(),
                ],
            ];
        }

        return [
            'redirect'   => true,
            'route'      => 'event_participant_step',
            'parameters' => [
                'participant' => $participant->getId(),
                'step'        => $registrationTemplate->getNextBlockPosition(
                    $participant->getRegistrationStep()
                ),
            ],
        ];
    }
}
