<?php

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

        $lastUpdatedAtExtraData = $this->eventExtraDataRepository->getExtraDataForEvent(
            $event,
            EventExtraDataType::LENI_GET_LAST_UPDATED_AT
        );

        $lastUpdatedAt = $lastUpdatedAtExtraData instanceof Event\ExtraData
            ? $lastUpdatedAtExtraData->getValue()
            : null;
        $fields = $this->fieldsByEventQueryHandler->handle(new FieldsByEventQuery($typesMapping, $customDataMapping));
        $filters = $this->getFilters($event, $lastUpdatedAt);
        $sort = $this->getSort();

        $newLastUpdatedAt = $this->getBatchUserData(
            $event,
            $lastUpdatedAt,
            $fields,
            $filters,
            $sort,
            $types,
            $typesMapping,
            $customDataMapping,
            0
        );

        if (null !== $newLastUpdatedAt) {
            $this->addOrUpdateEventExtraDataHandler->handle(
                new AddOrUpdateEventExtraData($event, EventExtraDataType::LENI_GET_LAST_UPDATED_AT, $newLastUpdatedAt)
            );
        }
    }

    /**
     * @param Event       $event
     * @param null|string $lastUpdatedAt
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
        ?string $lastUpdatedAt,
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

        $newLastUpdatedAt = null;

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

            $newLastUpdatedAt = $rawUserData[LeniConstants::LENI_COL_UPDATED_AT];
        }

        if (!empty($sheetIds)) {
            $this->jobQueue->indexSheets(array_values($sheetIds));
        }

        if (self::BATCH_LENGTH === \count($rawUsersData)) {
            unset($rawUsersData, $sheetIds);

            $newLastUpdatedAt = $this->getBatchUserData(
                $event,
                $lastUpdatedAt,
                $fields,
                $filters,
                $sort,
                $types,
                $typesMapping,
                $customDataMapping,
                $start + self::BATCH_LENGTH
            );
        }

        if (null === $newLastUpdatedAt) {
            return $lastUpdatedAt;
        }

        return $newLastUpdatedAt;
    }

    private function getFilters(Event $event, ?string $lastUpdatedAt): array
    {
        $filters = [];

        if (null !== $lastUpdatedAt) {
            $filters[] = $this->getFilterGreaterOrEqualLastUpdatedAt($lastUpdatedAt);
        }

        foreach ($this->getPredefinedFilters($event) as $filter) {
            $filters[] = $filter;
        }

        return $filters;
    }

    private function getFilterGreaterOrEqualLastUpdatedAt(string $lastUpdatedAt): array
    {
        return [
            LeniConstants::FILTER_FIELD => LeniConstants::LENI_COL_UPDATED_AT,
            LeniConstants::FILTER_OPERATOR => LeniConstants::FILTER_OPERATOR_GREATER_OR_EQUAL,
            LeniConstants::FILTER_VALUE => $lastUpdatedAt,
        ];
    }

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

    private function getSort(): array
    {
        return [LeniConstants::LENI_COL_UPDATED_AT => LeniConstants::SORT_ASC];
    }
}
