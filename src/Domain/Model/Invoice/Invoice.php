<?php

namespace Proximum\Vimeet\Domain\Model\Invoice;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Sheet;

/**
 * "Facture"
 */
class Invoice
{
    /** @var int */
    private $id;

    /** @var Event */
    private $event;

    /** @var Sheet */
    private $sheet;

    /** @var Prefix */
    private $prefix;

    /** @var string */
    private $invoicePrefix;

    /** @var int */
    private $invoiceYear;

    /** @var int */
    private $invoiceIncrement;

    /** @var int in cents */
    private $total;

    /** @var int in cents */
    private $totalWithVat;

    /** @var int in cents */
    private $vatAmount;

    /** @var string 3-letter ISO 4217 currency name */
    private $currency;

    /** @var \DateTimeInterface */
    private $createdAt;

    /** @var string */
    private $data;

    /** @var ArrayCollection of Order */
    private $orders;

    /** @var bool */
    private $vatApplicable;

    /** @var string 'ati'|'et' ; See Proximum\Vimeet\Domain\Model\Event VAT_MODE_ATI and VAT_MODE_ET */
    private $vatMode;

    /** @var float */
    private $vatRate;

    /**
     * @param Event              $event
     * @param Sheet              $sheet
     * @param Prefix             $prefix
     * @param string             $invoicePrefix
     * @param int                $invoiceYear
     * @param int                $invoiceIncrement
     * @param bool               $vatApplicable
     * @param string             $vatMode
     * @param float              $vatRate
     * @param int                $total
     * @param int                $totalWithVat
     * @param int                $vatAmount
     * @param string             $currency
     * @param string             $data
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(
        Event $event,
        Sheet $sheet,
        Prefix $prefix,
        $invoicePrefix,
        $invoiceYear,
        $invoiceIncrement,
        $vatApplicable,
        $vatMode,
        $vatRate,
        $total,
        $totalWithVat,
        $vatAmount,
        $currency,
        $data,
        \DateTimeInterface $createdAt
    ) {
        $this->event = $event;
        $this->sheet = $sheet;
        $this->prefix = $prefix;
        $this->invoicePrefix = $invoicePrefix;
        $this->invoiceYear = $invoiceYear;
        $this->invoiceIncrement = $invoiceIncrement;
        $this->vatApplicable = $vatApplicable;
        $this->total = $total;
        $this->totalWithVat = $totalWithVat;
        $this->vatAmount = $vatAmount;
        $this->currency = $currency;
        $this->data = $data;
        $this->createdAt = $createdAt;
        $this->orders = new ArrayCollection();
        $this->vatMode = $vatMode;
        $this->vatRate = $vatRate;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @return Prefix
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return int
     */
    public function getTotalWithVat()
    {
        return $this->totalWithVat;
    }

    /**
     * @return int
     */
    public function getVatAmount()
    {
        return $this->vatAmount;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getInvoicePrefix()
    {
        return $this->invoicePrefix;
    }

    /**
     * @return int
     */
    public function getInvoiceYear()
    {
        return $this->invoiceYear;
    }

    /**
     * @return int
     */
    public function getInvoiceIncrement()
    {
        return $this->invoiceIncrement;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return sprintf(
            '%s%s-%s',
            $this->getInvoicePrefix(),
            $this->getInvoiceYear(),
            str_pad($this->getInvoiceIncrement(), 4, '0', STR_PAD_LEFT)
        );
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return Order[]
     */
    public function getOrders()
    {
        return $this->orders->toArray();
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

    /**
     * @return string
     */
    public function getHash()
    {
        return sprintf(
            '%s-%s',
            $this->getNumber(),
            hash('sha256', $this->getId().$this->getNumber().$this->getCreatedAt()->format('YmdHis'))
        );
    }

    public function updateData(string $data): void
    {
        $this->data = $data;
    }
}
