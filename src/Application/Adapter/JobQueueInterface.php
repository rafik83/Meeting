<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
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

    /**
     * @param int[] $sheetIds
     * @param Admin $admin
     */
    public function generateInvoice(array $sheetIds, Admin $admin);
  
    /**
     * @param Event  $event
     * @param Admin  $admin
     * @param string $locale
     */
    public function exportOrdersForEvent(Event $event, Admin $admin, $locale);
}
