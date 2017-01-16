<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @param ArrayCollection|Sheet[]
     *
     * @return ArrayCollection|BillingInfo[]
     */
    public function getBySheets($sheets);

    /**
     * @param BillingInfo $billingInfo
     */
    public function add(BillingInfo $billingInfo);

    /**
     * @param BillingInfo $billingInfo
     */
    public function set(BillingInfo $billingInfo);
}
