<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Registration;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject;

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
        $participant->setRegistrationStep($this->getLastCompleteStep($participant));

        $this->participantRepository->set($participant);
    }

    /**
     * @param Participant $participant
     *
     * @return bool
     */
    private function isRegistrationComplete(Participant $participant): bool
    {
        $registrationTemplate = $this->templateDataFactory->createRegistrationFromParticipant(
            $participant,
            $participant->getLocale()
        );

        return !$this->hasEmptyRequiredObject($registrationTemplate->getEditableObjects());
    }

    /**
     * @param TemplateObject[] $editableObjects
     *
     * @return bool
     */
    private function hasEmptyRequiredObject(array $editableObjects): bool
    {
        foreach ($editableObjects as $editableObject) {
            if (true === $editableObject->getRequired() && $editableObject->isEmpty()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Participant $participant
     *
     * @return int
     */
    private function getLastCompleteStep(Participant $participant): int
    {
        $registrationTemplate = $this->templateDataFactory->createRegistrationFromParticipant(
            $participant,
            $participant->getLocale()
        );

        $step = 0;

        foreach ($registrationTemplate->getBlocks() as $block) {
            if ($this->hasEmptyRequiredObject($block->getEditableObjects())) {
                return $step;
            }

            ++$step;
        }

        return $registrationTemplate->getBlocksCount();
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

        $nextStep = $registrationTemplate->getNextBlockPosition($this->getLastCompleteStep($participant));

        return [
            'redirect'   => true,
            'route'      => 'event_participant_step',
            'parameters' => [
                'participant' => $participant->getId(),
                'step'        => null !== $nextStep ?
                    $nextStep :
                    $registrationTemplate->getBlocksCount(),
            ],
        ];
    }
}
