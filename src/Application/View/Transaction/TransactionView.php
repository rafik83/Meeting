<?php

namespace Proximum\Vimeet\Application\View\Transaction;

use Proximum\Vimeet\Domain\Model\Event;

class TransactionView
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var int
     */
    public $sheetId;

    /**
     * @var int
     */
    public $eventId;

    /**
     * @var string
     */
    public $eventName;

    /**
     * @var int
     */
    public $sheetOwnerId;

    /**
     * @var string
     */
    public $companyName;

    /**
     * @var \DateTimeInterface
     */
    public $transactionDate;

    /**
     * @var int
     */
    public $transactionType;

    /**
     * @var string
     */
    public $transactionReference;

    /**
     * @var string|null
     */
    public $transactionGateway;

    /**
     * @var float
     */
    public $transactionAmount;

    /**
     * @var string
     */
    public $contactBillingInfoCountry;

    /**
     * @var string
     */
    public $vatNumber;

    /**
     * TransactionView constructor.
     *
     * @param Event              $event
     * @param int                $sheetId
     * @param int                $eventId
     * @param string             $eventName
     * @param int                $sheetOwnerId
     * @param string             $companyName
     * @param \DateTimeInterface $transactionDate
     * @param int                $transactionType
     * @param string|null        $transactionReference
     * @param string|null        $transactionGateway
     * @param float              $transactionAmount
     * @param string|null        $contactBillingInfoCountry
     * @param string|null        $vatNumber
     */
    public function __construct(
        Event $event,
        $sheetId,
        $eventId,
        $eventName,
        $sheetOwnerId,
        $companyName,
        \DateTimeInterface $transactionDate,
        $transactionType,
        $transactionReference,
        $transactionGateway,
        $transactionAmount,
        $contactBillingInfoCountry = null,
        $vatNumber = null
    ) {
        $this->event                     = $event;
        $this->sheetId                   = $sheetId;
        $this->eventId                   = $eventId;
        $this->eventName                 = $eventName;
        $this->sheetOwnerId              = $sheetOwnerId;
        $this->companyName               = $companyName;
        $this->transactionDate           = $transactionDate;
        $this->transactionType           = $transactionType;
        $this->transactionReference      = $transactionReference;
        $this->transactionGateway        = $transactionGateway;
        $this->transactionAmount         = $transactionAmount;
        $this->contactBillingInfoCountry = $contactBillingInfoCountry;
        $this->vatNumber                 = $vatNumber;
    }
}
