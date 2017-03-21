<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Transaction;

class TransactionView
{
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
    public $societyName;
    
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
     * @param int                   $sheetId
     * @param int                   $eventId
     * @param string                $eventName
     * @param int                   $sheetOwnerId
     * @param string                $societyName
     * @param \DateTimeInterface    $transactionDate
     * @param int                   $transactionType
     * @param string                $transactionReference
     * @param null|string           $transactionGateway
     * @param float                 $transactionAmount
     * @param string                $contactBillingInfoCountry
     * @param string                $vatNumber
     */
    public function __construct(
        $sheetId,
        $eventId,
        $eventName,
        $sheetOwnerId,
        $societyName,
        \DateTimeInterface $transactionDate,
        $transactionType,
        $transactionReference,
        $transactionGateway,
        $transactionAmount,
        $contactBillingInfoCountry,
        $vatNumber
    ) {
        $this->sheetId                      = $sheetId;
        $this->eventId                      = $eventId;
        $this->eventName                    = $eventName;
        $this->sheetOwnerId                 = $sheetOwnerId;
        $this->societyName                  = $societyName;
        $this->transactionDate              = $transactionDate;
        $this->transactionType              = $transactionType;
        $this->transactionReference         = $transactionReference;
        $this->transactionGateway           = $transactionGateway;
        $this->transactionAmount            = $transactionAmount;
        $this->contactBillingInfoCountry    = $contactBillingInfoCountry;
        $this->vatNumber                    = $vatNumber;
    }
}
