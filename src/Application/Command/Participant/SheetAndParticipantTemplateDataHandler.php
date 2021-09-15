<?php

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\View\Participant\SheetAndParticipantTemplateDataView;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class SheetAndParticipantTemplateDataHandler
{
    /**
     * @param array        $dataIndexedByTag
     * @param TemplateData $registrationTemplateData
     * @param TemplateData $sheetTemplateData
     *
     * @throws \LogicException
     *
     * @return SheetAndParticipantTemplateDataView
     */
    public function handle(
        array &$dataIndexedByTag,
        TemplateData $registrationTemplateData,
        TemplateData $sheetTemplateData
    ): SheetAndParticipantTemplateDataView {
        $sheetAndParticipantTemplateDataView = $this->getSheetAndParticipantTemplateDataView(
            $dataIndexedByTag,
            $registrationTemplateData
        );

        $sheetAndParticipantTemplateDataView->sheetTemplateData = $this->getSheetTemplateData(
            $dataIndexedByTag,
            $sheetTemplateData
        );

        return $sheetAndParticipantTemplateDataView;
    }

    /**
     * @param array        $dataIndexedByTag
     * @param TemplateData $sheetTemplateData
     *
     * @return array
     */
    private function getSheetTemplateData(array &$dataIndexedByTag, TemplateData $sheetTemplateData): array
    {
        $editableObjects = $sheetTemplateData->getEditableObjects();

        $sheetTemplateTaggedData = $this->getSheetTemplateTaggedData($dataIndexedByTag);
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
     * @param array        $dataIndexedByTag
     * @param TemplateData $registrationTemplateData
     *
     * @throws \LogicException
     *
     * @return SheetAndParticipantTemplateDataView
     */
    private function getSheetAndParticipantTemplateDataView(
        array &$dataIndexedByTag,
        TemplateData $registrationTemplateData
    ): SheetAndParticipantTemplateDataView {
        $sheetRegistrationData = [];
        $participantRegistrationData = [];

        $editableObjects = $registrationTemplateData->getEditableObjects();

        $sheetTaggedData = $this->getSheetTaggedData($dataIndexedByTag);
        $this->setData($editableObjects, $sheetTaggedData);

        $participantTaggedData = $this->getParticipantTaggedData($dataIndexedByTag);
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
            $dataIndexedByTag[Tag::SHEET_TITLE] ?? '',
            $sheetRegistrationData,
            $participantRegistrationData
        );
    }

    /**
     * @param array $editableObjects
     * @param array $data
     *
     * @throws \LogicException
     */
    private function setData(array &$editableObjects, array &$data): void
    {
        foreach ($data as $tag => $value) {
            foreach ($editableObjects as $editableObject) {
                if (!$editableObject instanceof TemplateObject\ContentObjectInterface
                    || !$editableObject->hasTag($tag)
                ) {
                    continue;
                }

                if ($editableObject instanceof TemplateObject\Nomenclature) {
                    $this->handleNomenclatureObject($editableObject, $value);

                    continue;
                }

                if ($editableObject instanceof TemplateObject\EditableText) {
                    $this->handleEditableText($editableObject, $value);

                    continue;
                }

                $editableObject->setContentValue($value);
            }
        }
    }

    /**
     * @param TemplateObject\EditableText $editableText
     * @param mixed                       $value
     */
    private function handleEditableText(TemplateObject\EditableText $editableText, $value): void
    {
        if (\is_array($value) && $editableText->isTranslatable()) {
            $editableText->setTranslations($value);

            return;
        }

        $editableText->setContentValue($value);
    }

    /**
     * @param TemplateObject\Nomenclature $nomenclatureObject
     * @param null|string|array           $value
     */
    private function handleNomenclatureObject(TemplateObject\Nomenclature $nomenclatureObject, $value): void
    {
        if (null === $value) {
            return;
        }

        if (\is_array($value)) {
            $availableKeys = [];

            foreach ($value as $item) {
                if ($nomenclatureObject->hasKey($item)) {
                    $availableKeys[] = $item;
                }
            }

            $nomenclatureObject->setItems(array_values(array_unique($availableKeys)));

            return;
        }

        if ($nomenclatureObject->hasKey($value)) {
            $nomenclatureObject->setItems([$value]);

            return;
        }

        $nomenclatureObject->setContentValue($nomenclatureObject->getKeyForLabel($value));
    }

    /**
     * @param array $dataIndexedByTag
     *
     * @return array
     */
    private function getSheetTaggedData(array &$dataIndexedByTag): array
    {
        return $this->filterByTags($dataIndexedByTag, Tag::getSheetAndGenericTags());
    }

    /**
     * @param array $dataIndexedByTag
     *
     * @return array
     */
    private function getParticipantTaggedData(array &$dataIndexedByTag): array
    {
        return $this->filterByTags($dataIndexedByTag, Tag::getParticipantTags());
    }

    /**
     * @param array $dataIndexedByTag
     *
     * @return array
     */
    private function getSheetTemplateTaggedData(array &$dataIndexedByTag): array
    {
        return $this->filterByTags($dataIndexedByTag, Tag::getGenericSheetTemplateTags());
    }

    /**
     * @param array $dataIndexedByTag
     * @param array $tags
     *
     * @return array
     */
    private function filterByTags(array &$dataIndexedByTag, array $tags): array
    {
        return array_filter(
            $dataIndexedByTag,
            function ($key) use ($tags) {
                return \in_array($key, $tags, true);
            },
            ARRAY_FILTER_USE_KEY
        );
    }
}
