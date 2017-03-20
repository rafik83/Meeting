<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Proximum\Vimeet\Domain\Model\Type;

interface JobQueueInterface
{
    /**
     * @param Campaign $campaign
     */
    public function sendCampaign(Campaign $campaign);

    /**
     * @param Type[] $types
     * @param string $orderBy
     * @param string $emailToNotify
     * @param string $locale
     */
    public function printPlanning(array $types, $orderBy, $emailToNotify, $locale);
}
