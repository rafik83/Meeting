<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter\ElasticSearch\UserEventView;

use Proximum\Vimeet\Domain\Model\Event;

interface GetUserEventIdsByEventInterface
{
    /**
     * @return string[] elasticsearch document id
     */
    public function handle(Event $event): array;
}
