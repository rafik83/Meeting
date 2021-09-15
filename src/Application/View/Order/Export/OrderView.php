<?php

namespace Proximum\Vimeet\Application\View\Order\Export;

class OrderView
{
    /** @var int */
    public $orderId;

    /** @var string */
    public $orderDate;

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

    /** @var string */
    public $invoiceNumber;

    /** @var string */
    public $invoiceDate;

    /** @var float */
    public $totalWithoutVat;

    /** @var float */
    public $totalVat;

    /** @var float */
    public $totalWithVat;

    /**
     * @param int                       $orderId
     * @param string                    $orderDate
     * @param int                       $sheetId
     * @param string                    $sheetTitle
     * @param string                    $invoiceNumber
     * @param string                    $invoiceDate
     * @param BillingInfoView           $billingInfo
     * @param float                     $totalWithoutVat
     * @param float                     $totalVat
     * @param float                     $totalWithVat
     * @param ProductBoughtView[]       $productBoughtViews
     * @param PromotionCodeBoughtView[] $promotionCodeBoughtViews
     * @param CustomRowBoughtView[]     $customRowsViews
     */
    public function __construct(
        $orderId,
        $orderDate,
        $sheetId,
        $sheetTitle,
        $invoiceNumber,
        $invoiceDate,
        BillingInfoView $billingInfo,
        float $totalWithoutVat,
        float $totalVat,
        float $totalWithVat,
        array $productBoughtViews,
        array $promotionCodeBoughtViews,
        array $customRowsViews
    ) {
        $this->orderId = $orderId;
        $this->orderDate = $orderDate;
        $this->sheetId = $sheetId;
        $this->sheetTitle = $sheetTitle;
        $this->invoiceNumber = $invoiceNumber;
        $this->invoiceDate = $invoiceDate;
        $this->billingInfo = $billingInfo;
        $this->productBoughtViews = $productBoughtViews;
        $this->promotionCodeBoughtViews = $promotionCodeBoughtViews;
        $this->customRowsViews = $customRowsViews;
        $this->columnArray = [];
        $this->totalWithoutVat = $totalWithoutVat;
        $this->totalVat = $totalVat;
        $this->totalWithVat = $totalWithVat;
    }

    /**
     * @param array $data
     */
    public function setColumnArray(array $data): void
    {
        $this->columnArray = $data;
    }

    /**
     * @return int
     */
    public function countCustomRows(): int
    {
        return count($this->customRowsViews);
    }
}
