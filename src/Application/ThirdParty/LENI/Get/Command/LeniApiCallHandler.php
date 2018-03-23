<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Get\Command;

use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Api\LeniApiCaller;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\EventExtraParameter\MappingGetter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\LeniApiServerException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Converter\RawDataToParticipantConverter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Query\FieldsByEventQuery;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Query\FieldsByEventQueryHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type as EventExtraParameterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type as UserEventExtraDataType;

class LeniApiCallHandler
{
    private const BATCH_LENGTH = 100;

    /** @var LeniApiCaller */
    private $leniApi;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var MappingGetter */
    private $mappingGetter;

    /** @var FieldsByEventQueryHandler */
    private $fieldsByEventQueryHandler;

    /** @var RawDataToParticipantConverter */
    private $rawDataToParticipantConverter;

    public function __construct(
        LeniApiCaller $leniApi,
        EventRepositoryInterface $eventRepository,
        TypeRepositoryInterface $typeRepository,
        ExtraDataRepositoryInterface $extraDataRepository,
        MappingGetter $mappingGetter,
        FieldsByEventQueryHandler $fieldsByEventQueryHandler,
        RawDataToParticipantConverter $rawDataToParticipantConverter
    ) {
        $this->leniApi = $leniApi;
        $this->eventRepository = $eventRepository;
        $this->typeRepository = $typeRepository;
        $this->extraDataRepository = $extraDataRepository;
        $this->mappingGetter = $mappingGetter;
        $this->fieldsByEventQueryHandler = $fieldsByEventQueryHandler;
        $this->rawDataToParticipantConverter = $rawDataToParticipantConverter;
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
        $typesMapping = $this->mappingGetter->getMapping($event, EventExtraParameterType::TYPE_LENI_TYPES_MAPPING);

        if (null === $typesMapping) {
            return;
        }

        $types = $this->typeRepository->getTypesByEvent($event);

        $rawUsersData = $this->leniApi->get(
            $event,
            $this->fieldsByEventQueryHandler->handle(new FieldsByEventQuery($typesMapping)),
            $this->getFilters($event),
            0,
            self::BATCH_LENGTH
        );

        foreach ($rawUsersData as $rawUserData) {
            $this->rawDataToParticipantConverter->convert($event, $types, $typesMapping, $rawUserData);
        }
    }

    /**
     * @param Event $event
     *
     * @return array
     */
    private function getFilters(Event $event): array
    {
        return $this->ignorePreviouslyImportedUsers($event);
    }

    /**
     * @param Event $event
     *
     * @return array
     */
    private function ignorePreviouslyImportedUsers(Event $event): array
    {
        $usersExtraData = $this->extraDataRepository->getExtraDataForEventAndName(
            $event,
            UserEventExtraDataType::LENI_USER_ID
        );

        if (empty($usersExtraData)) {
            return [];
        }

        $usersId = array_map(
            function (ExtraData $extraData) {
                return $extraData->getValue();
            },
            $usersExtraData
        );

        return [
            [
                'selectedFieldId' => LeniConstants::LENI_COL_USER_ID,
                'selectedOperator' => 'NOT_IN',
                'value' => $usersId,
            ]
        ];
    }
}
