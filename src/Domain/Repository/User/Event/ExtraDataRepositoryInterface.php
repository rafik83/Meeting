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
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;

interface ExtraDataRepositoryInterface
{
    /**
     * @param Event $event
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
     * @param Event              $event
     * @param string             $name
     * @param array              $users
     * @param \DateTimeInterface $dateTime
     *
     * @return ExtraData[]
     */
    public function getForEventNameAndUsersOlderThanDate(Event $event, string $name, array $users, \DateTimeInterface $dateTime);
}
