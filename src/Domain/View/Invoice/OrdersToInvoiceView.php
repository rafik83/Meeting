<?php

namespace Proximum\Vimeet\Domain\View\Invoice;

use Proximum\Vimeet\Domain\Model\Order;

class OrdersToInvoiceView
{
    /** @var Order[] */
    private $orders;

    /** @var int amount in cents */
    private $total;

    /** @var int amount in cents */
    private $vatAmount;

    /** @var int amount in cents */
    private $totalWithVat;

    /** @var string InvoiceDataView serialized in json */
    private $data;

    /** @var string string 3-letter ISO 4217 currency name */
    private $currency;

    /** @var bool */
    private $vatApplicable;

    /** @var string 'ati'|'et' ; See Proximum\Vimeet\Domain\Model\Event VAT_MODE_ATI and VAT_MODE_ET */
    private $vatMode;

    /** @var float */
    private $vatRate;

    /**
     * @param array  $orders
     * @param string $data          InvoiceDataView serialized in json
     * @param bool   $vatApplicable
     * @param string $vatMode
     * @param float  $vatRate
     * @param int    $total         amount in cents
     * @param int    $vatAmount     amount in cents
     * @param int    $totalWithVat  amount in cents
     * @param string $currency
     */
    public function __construct(
        array $orders,
        string $data,
        bool $vatApplicable,
        string $vatMode,
        float $vatRate,
        int $total,
        int $vatAmount,
        int $totalWithVat,
        string $currency
    ) {
        $this->orders = $orders;
        $this->data = $data;
        $this->vatApplicable = $vatApplicable;
        $this->total = $total;
        $this->vatAmount = $vatAmount;
        $this->totalWithVat = $totalWithVat;
        $this->currency = $currency;
        $this->vatMode = $vatMode;
        $this->vatRate = $vatRate;
    }

    /**
     * @return Order[]
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @return int amount in cents
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return int amount in cents
     */
    public function getVatAmount()
    {
        return $this->vatAmount;
    }

    /**
     * @return int amount in cents
     */
    public function getTotalWithVat()
    {
        return $this->totalWithVat;
    }

    /**
     * @return string InvoiceDataView serialized in json
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return bool
     */
    public function isVatApplicable()
    {
        return $this->vatApplicable;
    }

    /**
     * @return string
     */
    public function getVatMode()
    {
        return $this->vatMode;
    }

    /**
     * @return float
     */
    public function getVatRate()
    {
        return $this->vatRate;
    }
}
