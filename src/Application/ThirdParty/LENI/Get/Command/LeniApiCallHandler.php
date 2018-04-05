<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Get\Command;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Event\ExtraData\AddOrUpdate as AddOrUpdateEventExtraData;
use Proximum\Vimeet\Application\Command\Event\ExtraData\AddOrUpdateHandler as AddOrUpdateEventExtraDataHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Api\LeniApiCaller;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\EventExtraParameter\MappingGetter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\LeniApiServerException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Converter\RawDataToParticipantConverter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Query\FieldsByEventQuery;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Query\FieldsByEventQueryHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Domain\Event\ExtraData\Type as EventExtraDataType;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type as EventExtraParameterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class LeniApiCallHandler
{
    private const BATCH_LENGTH = 100;

    /** @var LeniApiCaller */
    private $leniApi;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var ExtraParameterRepositoryInterface */
    private $extraParameterRepository;

    /** @var MappingGetter */
    private $mappingGetter;

    /** @var FieldsByEventQueryHandler */
    private $fieldsByEventQueryHandler;

    /** @var RawDataToParticipantConverter */
    private $rawDataToParticipantConverter;

    /** @var ExtraDataRepositoryInterface */
    private $eventExtraDataRepository;

    /** @var AddOrUpdateEventExtraDataHandler */
    private $addOrUpdateEventExtraDataHandler;

    /** @var JobQueueInterface */
    private $jobQueue;

    public function __construct(
        LeniApiCaller $leniApi,
        EventRepositoryInterface $eventRepository,
        TypeRepositoryInterface $typeRepository,
        ExtraParameterRepositoryInterface $extraParameterRepository,
        MappingGetter $mappingGetter,
        FieldsByEventQueryHandler $fieldsByEventQueryHandler,
        RawDataToParticipantConverter $rawDataToParticipantConverter,
        ExtraDataRepositoryInterface $eventExtraDataRepository,
        AddOrUpdateEventExtraDataHandler $addOrUpdateEventExtraDataHandler,
        JobQueueInterface $jobQueue
    ) {
        $this->leniApi = $leniApi;
        $this->eventRepository = $eventRepository;
        $this->typeRepository = $typeRepository;
        $this->extraParameterRepository = $extraParameterRepository;
        $this->mappingGetter = $mappingGetter;
        $this->fieldsByEventQueryHandler = $fieldsByEventQueryHandler;
        $this->rawDataToParticipantConverter = $rawDataToParticipantConverter;
        $this->eventExtraDataRepository = $eventExtraDataRepository;
        $this->addOrUpdateEventExtraDataHandler = $addOrUpdateEventExtraDataHandler;
        $this->jobQueue = $jobQueue;
    }

    /**
     * @param LeniApiCall $leniApiCall
     *
     * @throws LeniApiServerException
     */
    public function handle(LeniApiCall $leniApiCall): void
    {
        $events = $this->eventRepository->findEventWithParameters(
            [
                EventExtraParameterType::TYPE_LENI_USER,
                EventExtraParameterType::TYPE_LENI_EVENT,
            ]
        );

        foreach ($events as $event) {
            $this->getEventUsers($event);
        }
    }

    /**
     * @param Event $event
     *
     * @throws LeniApiServerException
     */
    private function getEventUsers(Event $event): void
    {
        $leniModeParameter = $this->extraParameterRepository->findByEventAndType(
            $event,
            EventExtraParameterType::TYPE_LENI_MODE
        );

        if ($leniModeParameter === null
            || !\in_array($leniModeParameter->getValue(), EventExtraParameterType::ALLOWED_LENI_MODE_FOR_GET, true)
        ) {
            return;
        }

        $typesMapping = $this->mappingGetter->getMapping($event, EventExtraParameterType::TYPE_LENI_TYPES_MAPPING);

        if (null === $typesMapping) {
            return;
        }

        $types = $this->typeRepository->getTypesByEvent($event);

        $customDataMapping = $this->mappingGetter->getMapping(
                $event,
                EventExtraParameterType::TYPE_LENI_DATA_MAPPING
            ) ?? [];

        $lastCreatedAtExtraData = $this->eventExtraDataRepository->getExtraDataForEvent(
            $event,
            EventExtraDataType::LENI_GET_LAST_CREATED_AT
        );

        $newLastCreatedAt = $this->getBatchUserData(
            $event,
            $lastCreatedAtExtraData instanceof Event\ExtraData ? $lastCreatedAtExtraData->getValue() : null,
            $types,
            $typesMapping,
            $customDataMapping,
            0
        );

        if (null !== $newLastCreatedAt) {
            $this->addOrUpdateEventExtraDataHandler->handle(
                new AddOrUpdateEventExtraData($event, EventExtraDataType::LENI_GET_LAST_CREATED_AT, $newLastCreatedAt)
            );
        }
    }

    /**
     * @param Event       $event
     * @param null|string $lastCreatedAt
     * @param Type[]      $types
     * @param array       $typesMapping
     * @param array       $customDataMapping
     * @param int         $start
     *
     * @return null|string
     * @throws LeniApiServerException
     */
    private function getBatchUserData(
        Event $event,
        ?string $lastCreatedAt,
        array &$types,
        array &$typesMapping,
        array &$customDataMapping,
        int $start
    ): ?string {
        $rawUsersData = $this->leniApi->get(
            $event,
            $this->fieldsByEventQueryHandler->handle(new FieldsByEventQuery($typesMapping, $customDataMapping)),
            $this->getFilters($typesMapping, $lastCreatedAt),
            $this->getSort(),
            $start,
            self::BATCH_LENGTH
        );

        $newLastCreatedAt = null;

        $sheetIds = [];

        foreach ($rawUsersData as $rawUserData) {
            $participant = $this->rawDataToParticipantConverter->convert(
                $event,
                $types,
                $typesMapping,
                $customDataMapping,
                $rawUserData
            );

            if ($participant instanceof Participant) {
                $sheetIds[$participant->getSheet()->getId()] = $participant->getSheet()->getId();
            }

            $newLastCreatedAt = $rawUserData[LeniConstants::LENI_COL_CREATED_AT];
        }

        if (!empty($sheetIds)) {
            $this->jobQueue->indexSheets(array_values($sheetIds));
        }

        if (\count($rawUsersData) === self::BATCH_LENGTH) {
            unset($rawUsersData);

            $newLastCreatedAt = $this->getBatchUserData(
                $event,
                $lastCreatedAt,
                $types,
                $typesMapping,
                $customDataMapping,
                $start + self::BATCH_LENGTH
            );
        }

        if (null === $newLastCreatedAt) {
            return $lastCreatedAt;
        }

        return $newLastCreatedAt;
    }

    /**
     * @param array       $typesMapping
     * @param null|string $lastCreatedAt
     *
     * @return array
     */
    private function getFilters(array &$typesMapping, ?string $lastCreatedAt): array
    {
        $filters = [];

        if (null !== $lastCreatedAt) {
            $filters[] = $this->getFilterGreaterOrEqualLastCreatedAt($lastCreatedAt);
        }

        $filterByLeniCategory = $this->getFiltersByLeniCategoryInTypeMappingValues($typesMapping);

        if (!empty($filterByLeniCategory)) {
            $filters[] = $filterByLeniCategory;
        }

        return $filters;
    }

    /**
     * @param string $lastCreatedAt
     *
     * @return array
     */
    private function getFilterGreaterOrEqualLastCreatedAt(string $lastCreatedAt): array
    {
        return [
            LeniConstants::FILTER_FIELD => LeniConstants::LENI_COL_CREATED_AT,
            LeniConstants::FILTER_OPERATOR => LeniConstants::FILTER_OPERATOR_GREATER_OR_EQUAL,
            LeniConstants::FILTER_VALUE => $lastCreatedAt,
        ];
    }

    /**
     * @param array $typesMapping
     *
     * @return array
     */
    private function getFiltersByLeniCategoryInTypeMappingValues(array &$typesMapping): array
    {
        $possibleCategoryValuesByFieldName = [];

        foreach ($typesMapping as $typeMapping) {
            if (\is_array($typeMapping)) {
                foreach ($typeMapping as $fieldName => $value) {
                    if ($fieldName !== LeniConstants::LENI_COL_CATEGORY) {
                        continue;
                    }

                    $possibleCategoryValuesByFieldName[$value] = $value;
                }
            }
        }

        if (!empty($possibleCategoryValuesByFieldName)) {
            return [
                LeniConstants::FILTER_FIELD => LeniConstants::LENI_COL_CATEGORY,
                LeniConstants::FILTER_OPERATOR => LeniConstants::FILTER_OPERATOR_IN,
                LeniConstants::FILTER_VALUE => array_values($possibleCategoryValuesByFieldName),
            ];
        }

        return [];
    }

    /**
     * @return array
     */
    private function getSort(): array
    {
        return [LeniConstants::LENI_COL_CREATED_AT => LeniConstants::SORT_ASC];
    }
}
