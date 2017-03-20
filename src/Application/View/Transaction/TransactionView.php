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
    public $society;
    
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
    public $tvaNumber;
    
    /**
     * TransactionView constructor.
     *
     * @param int                   $eventId
     * @param string                $eventName
     * @param int                   $sheetOwnerId
     * @param string                $society
     * @param \DateTimeInterface    $transactionDate
     * @param int                   $transactionType
     * @param string                $transactionReference
     * @param null|string           $transactionGateway
     * @param float                 $transactionAmount
     * @param string                $contactBillingInfoCountry
     * @param string                $tvaNumber
     */
    public function __construct(
        $eventId,
        $eventName,
        $sheetOwnerId,
        $society,
        \DateTimeInterface $transactionDate,
        $transactionType,
        $transactionReference,
        $transactionGateway,
        $transactionAmount,
        $contactBillingInfoCountry,
        $tvaNumber
    ) {
        $this->eventId                      = $eventId;
        $this->eventName                    = $eventName;
        $this->sheetOwnerId                 = $sheetOwnerId;
        $this->society                      = $society;
        $this->transactionDate              = $transactionDate;
        $this->transactionType              = $transactionType;
        $this->transactionReference         = $transactionReference;
        $this->transactionGateway           = $transactionGateway;
        $this->transactionAmount            = $transactionAmount;
        $this->contactBillingInfoCountry    = $contactBillingInfoCountry;
        $this->tvaNumber                    = $tvaNumber;
    }
}
