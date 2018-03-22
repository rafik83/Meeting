<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Get\Command;

use Proximum\Vimeet\Application\Command\Participant\ConvertToParticipant;
use Proximum\Vimeet\Application\Command\Participant\ConvertToParticipantHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Api\LeniApiCaller;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\EventExtraParameter\MappingGetter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\TemplateData\ParticipationTypeTemplateDataGetter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\LeniApiServerException;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Converter\TypeConverter;
use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type as EventExtraParameterType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
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

    /** @var TypeConverter */
    private $typeConverter;

    /** @var ConvertToParticipantHandler */
    private $convertToParticipantHandler;

    /** @var ParticipationTypeTemplateDataGetter */
    private $participationTypeTemplateDataGetter;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        LeniApiCaller $leniApi,
        EventRepositoryInterface $eventRepository,
        TypeRepositoryInterface $typeRepository,
        ExtraDataRepositoryInterface $extraDataRepository,
        MappingGetter $mappingGetter,
        TypeConverter $typeConverter,
        ConvertToParticipantHandler $convertToParticipantHandler,
        ParticipationTypeTemplateDataGetter $participationTypeTemplateDataGetter,
        \DateTimeInterface $dateTime
    ) {
        $this->leniApi = $leniApi;
        $this->eventRepository = $eventRepository;
        $this->typeRepository = $typeRepository;
        $this->extraDataRepository = $extraDataRepository;
        $this->mappingGetter = $mappingGetter;
        $this->convertToParticipantHandler = $convertToParticipantHandler;
        $this->typeConverter = $typeConverter;
        $this->participationTypeTemplateDataGetter = $participationTypeTemplateDataGetter;
        $this->dateTime = $dateTime;
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

        $rawUsers = $this->leniApi->get(
            $event,
            LeniConstants::LENI_GET_FIELDS,
            $this->getFilters($event),
            0,
            self::BATCH_LENGTH
        );

        foreach ($rawUsers as $rawUser) {
            $this->convertRawDataToParticipant($event, $types, $typesMapping, $rawUser);
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

    /**
     * @param Event  $event
     * @param Type[] $types
     * @param array  $typesMapping
     * @param array  $rawUser
     */
    private function convertRawDataToParticipant(
        Event $event,
        array &$types,
        array &$typesMapping,
        array &$rawUser
    ): void {
        $type = $this->convertType($types, $typesMapping, $rawUser);

        if (!$type instanceof Type) {
            return;
        }

        // @todo: set data for each tag
        $dataIndexedByTag = [];

        $participant = $this->convertToParticipantHandler->handle(
            new ConvertToParticipant(
                $event,
                $type,
                $rawUser[LeniConstants::LENI_COL_EMAIL],
                $rawUser[LeniConstants::LENI_COL_LOCALE],
                $dataIndexedByTag,
                $this->participationTypeTemplateDataGetter->getRegistrationTemplateDataByType($type),
                $this->participationTypeTemplateDataGetter->getSheetTemplateDataByType($type),
                UserEventExtraDataType::LENI_USER_ID
            )
        );

        if ($participant instanceof Participant) {
            $this->addLeniUserIdInUserEventExtraData(
                $event,
                $participant->getUser(),
                $rawUser[LeniConstants::LENI_COL_USER_ID]
            );
        }
    }

    /**
     * @param Event  $event
     * @param User   $user
     * @param string $leniId
     */
    private function addLeniUserIdInUserEventExtraData(Event $event, User $user, string $leniId): void
    {
        $this->extraDataRepository->add(
            new ExtraData(
                $user,
                $event,
                UserEventExtraDataType::LENI_USER_ID,
                $leniId,
                $this->dateTime
            )
        );
    }

    /**
     * @param Type[] $types
     * @param array  $typesMapping
     * @param array  $rawUser
     *
     * @return null|Type
     */
    private function convertType(array &$types, array &$typesMapping, array &$rawUser): ?Type
    {
        return $this->typeConverter->convert($types, $typesMapping, $rawUser);
    }
}
