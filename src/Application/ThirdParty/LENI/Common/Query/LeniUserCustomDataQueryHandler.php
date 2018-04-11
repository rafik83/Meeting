<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query;

use Proximum\Vimeet\Application\ThirdParty\LENI\Common\EventExtraParameter\MappingGetter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\TypeDoesNotMatchException;
use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Converter\CustomDataConverter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Converter\TypeConverter;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type as EventExtraParameterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class LeniUserCustomDataQueryHandler
{
    /** @var TypeConverter */
    private $typeConverter;

    /** @var MappingGetter */
    private $mappingGetter;

    /** @var CustomDataConverter */
    private $customDataConverter;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    public function __construct(
        TypeConverter $typeConverter,
        MappingGetter $mappingGetter,
        CustomDataConverter $customDataConverter,
        TemplateDataFactory $templateDataFactory
    ) {
        $this->typeConverter = $typeConverter;
        $this->mappingGetter = $mappingGetter;
        $this->customDataConverter = $customDataConverter;
        $this->templateDataFactory = $templateDataFactory;
    }

    /**
     * @throws TypeDoesNotMatchException
     */
    public function handle(LeniUserCustomDataQuery $leniUserCustomDataQuery): array
    {
        $data = $this->handleType($leniUserCustomDataQuery->event, $leniUserCustomDataQuery->type);

        $customData = $this->handleCustomData(
            $leniUserCustomDataQuery->event,
            $leniUserCustomDataQuery->sheet,
            $leniUserCustomDataQuery->user,
            $leniUserCustomDataQuery->locale
        );

        foreach ($customData as $fieldName => $value) {
            $data[$fieldName] = $value;
        }

        return $data;
    }

    /**
     * @throws TypeDoesNotMatchException
     */
    private function handleType(Event $event, Type $type): array
    {
        $typesMapping = $this->mappingGetter->getMapping(
            $event,
            EventExtraParameterType::TYPE_LENI_TYPES_MAPPING
        );

        if (null === $typesMapping) {
            return [];
        }

        $data = $this->typeConverter->convert($type, $typesMapping);

        if ([] === $data) {
            throw new TypeDoesNotMatchException();
        }

        return $data;
    }

    private function handleCustomData(Event $event, Sheet $sheet, User $user, string $locale): array
    {
        $customDataMapping = $this->mappingGetter->getMapping($event, EventExtraParameterType::TYPE_LENI_DATA_MAPPING);

        if (null === $customDataMapping) {
            return [];
        }

        $taggedData = [];

        $this->handleSheetState($sheet, $taggedData);
        $this->getTaggedRawData($this->templateDataFactory->createFromSheet($sheet, $locale), $taggedData);
        $this->getTaggedRawData($this->templateDataFactory->createRegistrationFromSheet($sheet, $locale), $taggedData);

        $participant = $sheet->getUserParticipant($user);

        if (null !== $participant) {
            $this->getTaggedRawData(
                $this->templateDataFactory->createRegistrationFromParticipant($participant, $locale),
                $taggedData
            );
        }

        return $this->customDataConverter->convert($customDataMapping, $taggedData);
    }

    private function handleSheetState(Sheet $sheet, array &$taggedData): void
    {
        $taggedData[Sheet::SHEET_STATE] = LeniConstants::SHEET_STATE_MAPPING[$sheet->getState()];
    }

    private function getTaggedRawData(TemplateData $templateData, array &$taggedData): void
    {
        foreach ($templateData->getEditableObjects() as $object) {
            foreach ($object->getTags() as $tag) {
                if (!$object instanceof TemplateObject\ContentObjectInterface) {
                    continue;
                }

                if ($object instanceof TemplateObject\Nomenclature) {
                    if ($object->isCheckboxes()) {
                        $taggedData[$tag] = $object->getItems();
                    } else {
                        $taggedData[$tag] = $object->getItem();
                    }
                } else {
                    $taggedData[$tag] = $object->getContentValueLocalize();
                }
            }
        }
    }
}
