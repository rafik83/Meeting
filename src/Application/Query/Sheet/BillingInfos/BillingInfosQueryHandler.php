<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\BillingInfos;

use Proximum\Vimeet\Application\View\Sheet\BillingInfos\BillingInfosView;

class BillingInfosQueryHandler
{
    /**
     * @param BillingInfosQuery $billingInfosQuery
     *
     * @return BillingInfosView
     */
    public function handle(BillingInfosQuery $billingInfosQuery)
    {
        return new BillingInfosView(
            $billingInfosQuery->lastname,
            $billingInfosQuery->firstname,
            $billingInfosQuery->function,
            $billingInfosQuery->phone,
            $billingInfosQuery->mobile,
            $billingInfosQuery->email,
            $billingInfosQuery->company,
            $billingInfosQuery->address,
            $billingInfosQuery->vatNumber,
            $billingInfosQuery->gender
        );
    }
}
