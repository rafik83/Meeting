<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\RegistrationView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\SheetAndParticipantTemplateDataView;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class SheetAndParticipantTemplateDataHandler
{
    /**
     * @param RegistrationView $registrationView
     * @param TemplateData     $registrationTemplateData
     *
     * @return SheetAndParticipantTemplateDataView
     */
    public function handle(
        RegistrationView $registrationView,
        TemplateData $registrationTemplateData
    ): SheetAndParticipantTemplateDataView {
        $sheetRegistrationData = [];
        $participantRegistrationData = [];

        $editableObjects = $registrationTemplateData->getEditableObjects();

        $sheetData = $this->getSheetData($registrationView);
        $this->setData($editableObjects, $sheetData);

        $participantData = $this->getParticipantData($registrationView);
        $this->setData($editableObjects, $participantData);

        foreach ($editableObjects as $editableObject) {
            if ($editableObject->hasTag(Tag::SHEET_DATA)) {
                $sheetRegistrationData[$editableObject->getKey()] = $editableObject->getData();
            }

            if ($editableObject->hasTag(Tag::PARTICIPANT_DATA)) {
                $participantRegistrationData[$editableObject->getKey()] = $editableObject->getData();
            }
        }

        return new SheetAndParticipantTemplateDataView(
            $sheetData[Tag::SHEET_TITLE],
            $sheetRegistrationData,
            $participantRegistrationData
        );
    }

    /**
     * @param array $editableObjects
     * @param array $data
     */
    private function setData(array &$editableObjects, array &$data): void
    {
        foreach ($data as $tag => $value) {
            foreach ($editableObjects as $editableObject) {
                if ($editableObject->hasTag($tag) && $editableObject instanceof TemplateObject\ContentObjectInterface) {
                    if ($editableObject instanceof TemplateObject\Nomenclature) {
                        $nomenclatureKey = $editableObject->getKeyForLabel($value);
                        $editableObject->setContentValue($nomenclatureKey);
                    } else {
                        $editableObject->setContentValue($value);
                    }
                }
            }
        }
    }

    /**
     * @param RegistrationView $registrationView
     *
     * @return array
     */
    private function getSheetData(RegistrationView $registrationView): array
    {
        return [
            Tag::SHEET_TITLE => $registrationView->companyName ?: $registrationView->participantView->getFullName(),
            Tag::SHEET_ORGANIZATION => $registrationView->companyName,
            Tag::SHEET_ADDRESS => $registrationView->address,
            Tag::SHEET_ZIPCODE => $registrationView->zipCode,
            Tag::SHEET_CITY => $registrationView->city,
            Tag::SHEET_COUNTRY => $registrationView->country,
            Tag::SHEET_PHONE => $registrationView->companyPhone,
            Tag::SHEET_WEBSITE => $registrationView->webSite,
        ];
    }

    /**
     * @param RegistrationView $registrationView
     *
     * @return array
     */
    private function getParticipantData(RegistrationView $registrationView): array
    {
        return [
            Tag::PARTICIPANT_GENDER => $registrationView->participantView->gender,
            Tag::PARTICIPANT_FIRSTNAME => $registrationView->participantView->firstName,
            Tag::PARTICIPANT_LASTNAME => $registrationView->participantView->lastName,
            Tag::PARTICIPANT_PHONE => $registrationView->participantView->phone,
            Tag::PARTICIPANT_ADDRESS => $registrationView->address,
            Tag::PARTICIPANT_ZIPCODE => $registrationView->zipCode,
            Tag::PARTICIPANT_CITY => $registrationView->city,
            Tag::PARTICIPANT_COUNTRY => $registrationView->country,
        ];
    }
}
