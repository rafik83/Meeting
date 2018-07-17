<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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

        if (null === $leniModeParameter
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

        $lastCreatedAt = $lastCreatedAtExtraData instanceof Event\ExtraData
            ? $lastCreatedAtExtraData->getValue()
            : null;
        $fields = $this->fieldsByEventQueryHandler->handle(new FieldsByEventQuery($typesMapping, $customDataMapping));
        $filters = $this->getFilters($event, $lastCreatedAt);
        $sort = $this->getSort();

        $newLastCreatedAt = $this->getBatchUserData(
            $event,
            $lastCreatedAt,
            $fields,
            $filters,
            $sort,
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
     * @param array       $fields
     * @param array       $filters
     * @param array       $sort
     * @param Type[]      $types
     * @param array       $typesMapping
     * @param array       $customDataMapping
     * @param int         $start
     *
     * @throws LeniApiServerException
     *
     * @return null|string
     */
    private function getBatchUserData(
        Event $event,
        ?string $lastCreatedAt,
        array &$fields,
        array &$filters,
        array &$sort,
        array &$types,
        array &$typesMapping,
        array &$customDataMapping,
        int $start
    ): ?string {
        $rawUsersData = $this->leniApi->get(
            $event,
            $fields,
            $filters,
            $sort,
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

        if (self::BATCH_LENGTH === \count($rawUsersData)) {
            unset($rawUsersData, $sheetIds);

            $newLastCreatedAt = $this->getBatchUserData(
                $event,
                $lastCreatedAt,
                $fields,
                $filters,
                $sort,
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

    private function getFilters(Event $event, ?string $lastCreatedAt): array
    {
        $filters = [];

        if (null !== $lastCreatedAt) {
            $filters[] = $this->getFilterGreaterOrEqualLastCreatedAt($lastCreatedAt);
        }

        foreach ($this->getPredefinedFilters($event) as $filter) {
            $filters[] = $filter;
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
     * @param Event $event
     *
     * @return array
     */
    private function getPredefinedFilters(Event $event): array
    {
        $predefinedFilters = $this->extraParameterRepository->findByEventAndType(
            $event,
            EventExtraParameterType::TYPE_LENI_PREDEFINED_FILTERS
        );

        if (null === $predefinedFilters) {
            return [];
        }

        $predefinedFiltersDecoded = json_decode($predefinedFilters->getValue(), true);

        if (!$predefinedFiltersDecoded) {
            return [];
        }

        return $predefinedFiltersDecoded;
    }

    /**
     * @return array
     */
    private function getSort(): array
    {
        return [LeniConstants::LENI_COL_CREATED_AT => LeniConstants::SORT_ASC];
    }
}
