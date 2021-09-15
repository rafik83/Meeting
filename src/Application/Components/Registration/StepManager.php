<?php

namespace Proximum\Vimeet\Application\Components\Registration;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
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
     * @param TemplateDataFactory            $templateDataFactory
     * @param ParticipantRepositoryInterface $participantRepository
     */
    public function __construct(
        TemplateDataFactory $templateDataFactory,
        ParticipantRepositoryInterface $participantRepository
    ) {
        $this->templateDataFactory = $templateDataFactory;
        $this->participantRepository = $participantRepository;
    }

    /**
     * @param Participant $participant
     * @param int         $step
     */
    public function updateCurrentStep(Participant $participant, $step): void
    {
        $participant->setRegistrationStep($step);

        $registrationTemplate = $this->templateDataFactory->createRegistrationFromParticipant(
            $participant,
            $participant->getLocale()
        );

        if (true === $this->isRegistrationComplete($registrationTemplate)) {
            $participant->setRegistrationComplete(true);
        }

        $this->participantRepository->set($participant);
    }

    /**
     * @param Participant $participant
     */
    public function resetRegistrationStep(Participant $participant): void
    {
        $registrationTemplate = $this->templateDataFactory->createRegistrationFromParticipant(
            $participant,
            $participant->getLocale()
        );

        $participant->setRegistrationComplete($this->isRegistrationComplete($registrationTemplate));
        $participant->setRegistrationStep($this->getLastCompleteStep($registrationTemplate));

        $this->participantRepository->set($participant);
    }

    /**
     * @param TemplateData $registrationTemplate
     *
     * @return bool
     */
    private function isRegistrationComplete(TemplateData $registrationTemplate): bool
    {
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
     * @param TemplateData $registrationTemplate
     *
     * @return int
     */
    private function getLastCompleteStep(TemplateData $registrationTemplate): int
    {
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
    public function getRedirectStep(Sheet $sheet, Participant $participant): array
    {
        $registrationTemplate = $this->templateDataFactory->createRegistrationFromParticipant(
            $participant,
            $participant->getLocale()
        );

        if (true === $this->isRegistrationComplete($registrationTemplate)) {
            return ['redirect' => false];
        }

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

        $nextStep = $registrationTemplate->getNextBlockPosition($this->getLastCompleteStep($registrationTemplate));

        return [
            'redirect'   => true,
            'route'      => 'event_participant_step',
            'parameters' => [
                'participant' => $participant->getId(),
                'step'        => $nextStep ?? $registrationTemplate->getBlocksCount(),
            ],
        ];
    }
}
