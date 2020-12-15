<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\User\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;

interface ExtraDataRepositoryInterface
{
    /**
     * @param Event  $event
     * @param string $name
     *
     * @return ExtraData[]
     */
    public function getExtraDataForEventAndName(Event $event, string $name): array;

    /**
     * @return ExtraData[]
     */
    public function getExtraDataForEventIdAndNameIndexedByUserId(int $eventId, string $name): array;

    public function getExtraDataForEventNameAndValue(
        Event $event,
        string $name,
        $value
    ): ?ExtraData;

    public function getExtraDataForEventNameAndMD5Value(
        Event $event,
        string $name,
        $value
    ): ?ExtraData;

    /**
     * @param ExtraData $extraData
     */
    public function add(ExtraData $extraData);

    /**
     * @param ExtraData $extraData
     */
    public function set(ExtraData $extraData);

    /**
     * @param ExtraData $extraData
     */
    public function remove(ExtraData $extraData): void;

    /**
     * @param $id
     *
     * @return null|ExtraData
     */
    public function getById($id): ?ExtraData;

    /**
     * @param Event  $event
     * @param string $name
     * @param User   $user
     *
     * @return null|ExtraData
     */
    public function getExtraDataForEventNameAndUser(Event $event, string $name, User $user): ?ExtraData;

    public function getExtraDataForEventIdNameAndUserId(int $eventId, string $name, int $userId): ?ExtraData;

    /**
     * @param Event              $event
     * @param string             $name
     * @param \DateTimeInterface $dateTime
     *
     * @return ExtraData[]
     */
    public function getForEventNameOlderThanDate(Event $event, string $name, \DateTimeInterface $dateTime): array;

    /**
     * @param Event[]            $events
     * @param string             $name
     * @param \DateTimeInterface $dateTime
     *
     * @return ExtraData[]
     */
    public function getForEventsAndNameWithOlderThanDate(array $events, string $name, \DateTimeInterface $dateTime): array;

    public function removeForUserAndEventAndName(User $user, Event $event, string $name): void;
}
