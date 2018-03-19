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
use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\LeniApiServerException;
use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type as UserEventExtraDataType;

class LeniApiCallHandler
{
    private const BATCH_LENGTH = 100;

    /** @var LeniApiCaller */
    private $leniApi;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    public function __construct(
        LeniApiCaller $leniApi,
        EventRepositoryInterface $eventRepository,
        ExtraDataRepositoryInterface $extraDataRepository
    ) {
        $this->leniApi = $leniApi;
        $this->eventRepository = $eventRepository;
        $this->extraDataRepository = $extraDataRepository;
    }

    /**
     * @param LeniApiCall $leniApiCall
     *
     * @throws LeniApiServerException
     */
    public function handle(LeniApiCall $leniApiCall): void
    {
        $events = $this->eventRepository->findEventWithParameters([Type::TYPE_LENI_USER, Type::TYPE_LENI_EVENT]);

        foreach ($events as $event) {
            $this->getEventUsers($event);
        }
    }

    /**
     * @param Event $event
     *
     * @throws LeniApiServerException
     */
    private function getEventUsers(Event $event)
    {
        $this->leniApi->get($event, $this->getFilters($event), 0, self::BATCH_LENGTH);
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
