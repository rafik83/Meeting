<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Type;

use Proximum\Vimeet\Application\Exception\Type\TypeAlreadyExistsException;
use Proximum\Vimeet\Application\Template\Registration\RegistrationTemplateCloner;
use Proximum\Vimeet\Application\Template\Sheet\SheetTemplateCloner;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class UpdateHandler
{
    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var SheetTemplateCloner */
    private $sheetTemplateCloner;

    /** @var RegistrationTemplateCloner */
    private $registrationTemplateCloner;

    public function __construct(
        TypeRepositoryInterface $typeRepository,
        SheetTemplateCloner $sheetTemplateCloner,
        RegistrationTemplateCloner $registrationTemplateCloner
    ) {
        $this->typeRepository = $typeRepository;
        $this->sheetTemplateCloner = $sheetTemplateCloner;
        $this->registrationTemplateCloner = $registrationTemplateCloner;
    }

    /**
     * @param Update $update
     *
     * @throws PackageNotRequiredException
     * @throws TypeAlreadyExistsException
     */
    public function handle(Update $update): void
    {
        if(!$update->isPackageRequired && $update->isPaymentRequired) {
            throw new PackageNotRequiredException();
        }

        $type = $update->type;
        $type->update(
            $update->rank,
            $update->hidden,
            $update->availabilityType,
            $update->numberOfMeetingsPerPlanning,
            $update->canMoveMeeting,
            $update->canRemoveMeeting,
            $update->areAllSheetParticipantsAssignedToMeeting,
            $update->canScanParticipant,
            $update->isPackageRequired,
            $update->isPaymentRequired,
            $update->priorityMeetingRequestsNumber,
            $update->numberMaxOfHappeningsPerUser,
            $update->numberMaxOfMeetingsPerSheet,
            $update->canEvaluateMeeting,
            $update->canEvaluateMeeting ? $update->mustEvaluateMeeting : false,
            $update->canSubmitValidation,
            $update->displayAnalyticsOnSheet,
            $update->displayAnalyticsOnMeetingList
        );
        $type->setHidden($update->hidden);

        if ($update->sheetTemplate !== $type->getSheetTemplate()) {
            $type->setSheetTemplate($this->getSheetTemplate($update, $type));
        }

        if ($update->registrationTemplate !== $type->getRegistrationTemplate()) {
            $type->setRegistrationTemplate($this->getRegistrationTemplate($update, $type));
        }

        if ($update->package !== $type->getPackage()) {
            $type->setPackage($update->package);
        }

        $type->setFormTemplates($update->formTemplates);

        $localesTitleAlreadyExists = [];

        foreach ($update->translations as $locale => $translation) {
            if ($this->typeRepository->typeExists(
                $update->type->getEvent(), $locale, $translation['title'], $update->type
            )) {
                $localesTitleAlreadyExists[] = $locale;
            } else {
                $type->translate($locale, $translation['title'], $translation['description']);
            }
        }

        if (!empty($localesTitleAlreadyExists)) {
            throw new TypeAlreadyExistsException($localesTitleAlreadyExists);
        }

        if (isset($update->validationCriteria['sheetAccepted'])) {
            $type->getValidationCriteria()->setSheetAccepted($update->validationCriteria['sheetAccepted']);
        }

        $this->typeRepository->set($type);
    }

    /**
     * @param Update $update
     * @param Type   $type
     *
     * @return SheetTemplate
     */
    private function getSheetTemplate(Update $update, Type $type): SheetTemplate
    {
        return $update->sheetTemplate->getEvent() === $update->type->getEvent()
            ? $update->sheetTemplate
            : $this->sheetTemplateCloner->duplicate(
                $update->sheetTemplate,
                $update->type->getEvent(),
                $type->getTitle($update->type->getEvent()->getAvailableLocale($update->locale))
            );
    }

    /**
     * @param Update $update
     * @param Type   $type
     *
     * @return RegistrationTemplate
     */
    private function getRegistrationTemplate(Update $update, Type $type): RegistrationTemplate
    {
        return $update->registrationTemplate->getEvent() === $update->type->getEvent()
            ? $update->registrationTemplate
            : $this->registrationTemplateCloner->duplicate(
                $update->registrationTemplate,
                $update->type->getEvent(),
                $type->getTitle($update->type->getEvent()->getAvailableLocale($update->locale))
            );
    }
}
