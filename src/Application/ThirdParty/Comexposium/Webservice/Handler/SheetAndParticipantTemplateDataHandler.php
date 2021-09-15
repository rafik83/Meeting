<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\Nomenclature\NomenclatureItemView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\RegistrationView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\SheetAndParticipantTemplateDataView;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class SheetAndParticipantTemplateDataHandler
{
    private const NOMENCLATURE_KEY_SUFFIX = 'cmxp_';

    /**
     * @param RegistrationView $registrationView
     * @param TemplateData     $registrationTemplateData
     * @param TemplateData     $sheetTemplateData
     *
     * @return SheetAndParticipantTemplateDataView
     */
    public function handle(
        RegistrationView $registrationView,
        TemplateData $registrationTemplateData,
        TemplateData $sheetTemplateData
    ): SheetAndParticipantTemplateDataView {
        $sheetAndParticipantTemplateDataView = $this->getSheetAndParticipantTemplateDataView(
            $registrationView,
            $registrationTemplateData
        );

        $sheetAndParticipantTemplateDataView->setSheetTemplateData(
            $this->getSheetTemplateData($registrationView, $sheetTemplateData)
        );

        return $sheetAndParticipantTemplateDataView;
    }

    /**
     * @param RegistrationView $registrationView
     * @param TemplateData     $sheetTemplateData
     *
     * @return array
     */
    private function getSheetTemplateData(RegistrationView $registrationView, TemplateData $sheetTemplateData): array
    {
        $editableObjects = $sheetTemplateData->getEditableObjects();

        $sheetTemplateTaggedData = $this->getSheetTemplateTaggedData($registrationView);
        $this->setData($editableObjects, $sheetTemplateTaggedData);

        $data = [];

        foreach ($editableObjects as $editableObject) {
            if (!empty($editableObject->getData())) {
                $data[$editableObject->getKey()] = $editableObject->getData();
            }
        }

        return $data;
    }

    /**
     * @param RegistrationView $registrationView
     * @param TemplateData     $registrationTemplateData
     *
     * @return SheetAndParticipantTemplateDataView
     */
    private function getSheetAndParticipantTemplateDataView(
        RegistrationView $registrationView,
        TemplateData $registrationTemplateData
    ): SheetAndParticipantTemplateDataView {
        $sheetRegistrationData = [];
        $participantRegistrationData = [];

        $editableObjects = $registrationTemplateData->getEditableObjects();

        $sheetTaggedData = $this->getSheetTaggedData($registrationView);
        $this->setData($editableObjects, $sheetTaggedData);

        $participantTaggedData = $this->getParticipantTaggedData($registrationView);
        $this->setData($editableObjects, $participantTaggedData);

        foreach ($editableObjects as $editableObject) {
            if ($editableObject->hasTag(Tag::SHEET_DATA)) {
                $sheetRegistrationData[$editableObject->getKey()] = $editableObject->getData();
            }

            if ($editableObject->hasTag(Tag::PARTICIPANT_DATA)) {
                $participantRegistrationData[$editableObject->getKey()] = $editableObject->getData();
            }
        }

        return new SheetAndParticipantTemplateDataView(
            $sheetTaggedData[Tag::SHEET_TITLE] ?? '',
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
                        $this->handleNomenclatureObject($editableObject, $value);

                        continue;
                    }

                    if ($editableObject instanceof TemplateObject\EditableText
                        && $editableObject->isTranslatable()
                        && \is_array($value)
                    ) {
                        $editableObject->setTranslations($value);

                        continue;
                    }

                    $editableObject->setContentValue($value);
                }
            }
        }
    }

    /**
     * @param TemplateObject\Nomenclature $nomenclatureObject
     * @param null|string|array           $value
     */
    private function handleNomenclatureObject(TemplateObject\Nomenclature $nomenclatureObject, $value): void
    {
        if (\is_array($value)) {
            $availableKeys = [];

            foreach ($value as $item) {
                $foundKey = $this->handleNomenclatureItemView($nomenclatureObject, $item);

                if (null !== $foundKey) {
                    $availableKeys[] = $foundKey;
                }
            }

            $nomenclatureObject->setItems(array_values(array_unique($availableKeys)));

            return;
        }

        $nomenclatureKey = $nomenclatureObject->getKeyForLabel($value);
        $nomenclatureObject->setContentValue($nomenclatureKey);
    }

    /**
     * @param TemplateObject\Nomenclature $nomenclatureObject
     * @param NomenclatureItemView        $nomenclatureItemView
     *
     * @return null|string
     */
    private function handleNomenclatureItemView(
        TemplateObject\Nomenclature $nomenclatureObject,
        NomenclatureItemView $nomenclatureItemView
    ): ?string {
        if (!$nomenclatureItemView instanceof NomenclatureItemView) {
            return null;
        }

        $key = sprintf('%s%s', self::NOMENCLATURE_KEY_SUFFIX, $nomenclatureItemView->reference);

        if ($nomenclatureObject->hasKey($key)) {
            return $key;
        }

        if ($nomenclatureItemView->parentNomenclatureItemView instanceof NomenclatureItemView) {
            return $this->handleNomenclatureItemView(
                $nomenclatureObject,
                $nomenclatureItemView->parentNomenclatureItemView
            );
        }

        return null;
    }

    /**
     * @param RegistrationView $registrationView
     *
     * @return array
     */
    private function getSheetTaggedData(RegistrationView $registrationView): array
    {
        $descriptionTranslations = [];

        foreach ($registrationView->registrationDescriptionViews as $registrationDescriptionView) {
            $descriptionTranslations[$registrationDescriptionView->locale] = $registrationDescriptionView->label;
        }

        return [
            Tag::SHEET_TITLE => $registrationView->companyName ?: $registrationView->participantView->getFullName(),
            Tag::SHEET_ORGANIZATION => $registrationView->companyName,
            Tag::SHEET_ADDRESS => $registrationView->address,
            Tag::SHEET_ZIPCODE => $registrationView->zipCode,
            Tag::SHEET_CITY => $registrationView->city,
            Tag::SHEET_COUNTRY => $registrationView->country,
            Tag::SHEET_PHONE => $registrationView->companyPhone,
            Tag::SHEET_WEBSITE => $registrationView->webSite,
            Tag::SHEET_DESCRIPTION => $descriptionTranslations,
        ];
    }

    /**
     * @param RegistrationView $registrationView
     *
     * @return array
     */
    private function getParticipantTaggedData(RegistrationView $registrationView): array
    {
        $positionTranslations = [];

        foreach ($registrationView->participantView->participantPositionViews as $participantPositionView) {
            $positionTranslations[$participantPositionView->locale] = $participantPositionView->label;
        }

        return [
            Tag::PARTICIPANT_GENDER => $registrationView->participantView->gender,
            Tag::PARTICIPANT_FIRSTNAME => $registrationView->participantView->firstName,
            Tag::PARTICIPANT_LASTNAME => $registrationView->participantView->lastName,
            Tag::PARTICIPANT_PHONE => $registrationView->participantView->phone,
            Tag::PARTICIPANT_ADDRESS => $registrationView->address,
            Tag::PARTICIPANT_ZIPCODE => $registrationView->zipCode,
            Tag::PARTICIPANT_CITY => $registrationView->city,
            Tag::PARTICIPANT_COUNTRY => $registrationView->country,
            Tag::PARTICIPANT_POSITION => $positionTranslations,
        ];
    }

    /**
     * @param RegistrationView $registrationView
     *
     * @return array
     */
    private function getSheetTemplateTaggedData(RegistrationView $registrationView): array
    {
        return [
            'sheet_template_generic_tag_1' => $registrationView->nomenclatureItemViews,
        ];
    }
}
