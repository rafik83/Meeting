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

interface ExtraParametersRepositoryInterface
{
    /**
     * @param Event $event
     *
     * @return Event\ExtraParameters[]
     */
    public function findByEvent(Event $event);

    /**
     * @param Event\ExtraParameters $extraParameters
     */
    public function set(Event\ExtraParameters $extraParameters);

    /**
     * @param Event\ExtraParameters $extraParameters
     */
    public function add(Event\ExtraParameters $extraParameters);

    /**
     * @param Event\ExtraParameters $extraParameters
     */
    public function remove(Event\ExtraParameters $extraParameters);

    /**
     * @param Event  $event
     * @param string $type
     *
     * @return null|Event\ExtraParameters
     */
    public function findByEventAndType(Event $event, string $type);
}
