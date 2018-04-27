<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Messaging;

use Proximum\Vimeet\Domain\Model\Event;

interface CampaignRepositoryInterface
{
    /**
     * Add a new messaging campaign.
     *
     * @param Campaign $campaign
     */
    public function add(Campaign $campaign);

    /**
     * Update a given campaign.
     *
     * @param Campaign $campaign
     */
    public function set(Campaign $campaign);

    /**
     * Find all campaigns for a given event.
     *
     * @param Event $event
     *
     * @return Campaign[]
     */
    public function findByEvent(Event $event);

    /**
     * @param int $id
     *
     * @return Campaign
     */
    public function getById($id);
}
