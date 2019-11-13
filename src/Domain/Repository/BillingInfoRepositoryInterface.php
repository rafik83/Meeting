<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

interface BillingInfoRepositoryInterface
{
    /**
     * @param Sheet $sheet
     *
     * @return null|BillingInfo
     */
    public function getBySheet(Sheet $sheet);

    /**
     * @param Sheet[] $sheets
     */
    public function loadBySheets(array $sheets): void;

    /**
     * @param Sheet[] $sheets
     *
     * @return BillingInfo[]
     */
    public function getBySheets(array $sheets);

    /**
     * @param BillingInfo $billingInfo
     */
    public function add(BillingInfo $billingInfo);

    /**
     * @param BillingInfo $billingInfo
     */
    public function set(BillingInfo $billingInfo);

    /**
     * @param Event $event
     *
     * @return BillingInfo[]
     */
    public function findByEvent(Event $event);
}
