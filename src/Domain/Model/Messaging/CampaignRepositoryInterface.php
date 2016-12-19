<?php

/*
 * This file is part of the Proximum Vimeet website.
 *
 * Copyright © Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Messaging;

use Proximum\Vimeet\Domain\Model\Event;

interface CampaignRepositoryInterface
{
    /**
     * Find all campaigns for a given event.
     *
     * @param Event $event
     *
     * @return Campaign[]
     */
    public function findByEvent(Event $event);
}
