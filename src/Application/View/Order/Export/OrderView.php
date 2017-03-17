<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Order\Export;

class OrderView
{
    /** @var int */
    public $orderId;

    /** @var int */
    public $sheetId;

    /** @var string */
    public $sheetTitle;

    /** @var BillingInfoView */
    public $billingInfo;

    /** @var ProductBoughtView[] */
    public $productBoughtViews;

    /** @var CustomRowView[] */
    public $customRowsViews;

    /**
     * @param int                 $orderId
     * @param int                 $sheetId
     * @param string              $sheetTitle
     * @param BillingInfoView     $billingInfo
     * @param ProductBoughtView[] $productBougthViews
     * @param CustomRowView[]     $customRowsViews
     */
    public function __construct(
        $orderId,
        $sheetId,
        $sheetTitle,
        BillingInfoView $billingInfo,
        array $productBougthViews,
        array $customRowsViews
    ) {
        $this->orderId            = $orderId;
        $this->sheetId            = $sheetId;
        $this->sheetTitle         = $sheetTitle;
        $this->billingInfo        = $billingInfo;
        $this->productBoughtViews = $productBougthViews;
        $this->customRowsViews    = $customRowsViews;
    }
}
