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

    /** @var CustomRowBoughtView[] */
    public $customRowsViews;

    /** @var array */
    public $columnArray;

    /** @var PromotionCodeBoughtView[] */
    public $promotionCodeBoughtViews;

    /**
     * @param int                       $orderId
     * @param int                       $sheetId
     * @param string                    $sheetTitle
     * @param BillingInfoView           $billingInfo
     * @param ProductBoughtView[]       $productBoughtViews
     * @param PromotionCodeBoughtView[] $promotionCodeBoughtViews
     * @param CustomRowBoughtView[]     $customRowsViews
     */
    public function __construct(
        $orderId,
        $sheetId,
        $sheetTitle,
        BillingInfoView $billingInfo,
        array $productBoughtViews,
        array $promotionCodeBoughtViews,
        array $customRowsViews
    ) {
        $this->orderId                  = $orderId;
        $this->sheetId                  = $sheetId;
        $this->sheetTitle               = $sheetTitle;
        $this->billingInfo              = $billingInfo;
        $this->productBoughtViews       = $productBoughtViews;
        $this->promotionCodeBoughtViews = $promotionCodeBoughtViews;
        $this->customRowsViews          = $customRowsViews;
        $this->columnArray              = [];
    }

    /**
     * @param array $data
     */
    public function setColumnArray(array $data)
    {
        $this->columnArray = $data;
    }

    /**
     * @return int
     */
    public function countCustomRows()
    {
        return count($this->customRowsViews);
    }
}
