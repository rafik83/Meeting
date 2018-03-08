<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\ExtraData;

interface ExtraDataRepositoryInterface
{
    /**
     * @param ExtraData $extraData
     */
    public function add(ExtraData $extraData);

    /**
     * @param ExtraData $extraData
     */
    public function set(ExtraData $extraData);

    /**
     * @param Event  $event
     * @param string $name
     *
     * @return null|ExtraData
     */
    public function getExtraDataForEvent(Event $event, string $name): ?ExtraData;
}
