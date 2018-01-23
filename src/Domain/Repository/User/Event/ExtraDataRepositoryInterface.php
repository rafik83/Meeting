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
     * @param ExtraData $extraData
     */
    public function add(ExtraData $extraData);

    /**
     * @param ExtraData $extraData
     */
    public function set(ExtraData $extraData);

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

    /**
     * @param Event              $event
     * @param string             $name
     * @param \DateTimeInterface $dateTime
     *
     * @return ExtraData[]
     */
    public function getForEventNameOlderThanDate(Event $event, string $name, \DateTimeInterface $dateTime): array;
}
