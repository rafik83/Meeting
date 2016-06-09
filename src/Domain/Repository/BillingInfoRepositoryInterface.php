<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\Sheet;

interface BillingInfoRepositoryInterface
{
    /**
     * @param Sheet $sheet
     *
     * @return BillingInfo
     */
    public function getBySheet(Sheet $sheet);

    /**
     * @param BillingInfo $billingInfo
     */
    public function add(BillingInfo $billingInfo);

    /**
     * @param BillingInfo $billingInfo
     */
    public function set(BillingInfo $billingInfo);
}
