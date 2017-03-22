<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Transaction;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\View\Transaction\TransactionView;
use Proximum\Vimeet\Domain\Model\Payment\Payment;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;

class TransactionViewQueryHandler
{
    const PAYPAL_TRANSACTION_ID_KEY = 'PAYMENTINFO_0_TRANSACTIONID';
    
    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;
    
    /**
     * @var BillingInfoRepositoryInterface
     */
    private $billingInfoRepository;
    
    /**
     * TransactionViewQueryHandler constructor.
     *
     * @param SheetInfoGuesser $sheetInfoGuesser
     * @param BillingInfoRepositoryInterface $billingInfoRepository
     */
    public function __construct(
        SheetInfoGuesser $sheetInfoGuesser,
        BillingInfoRepositoryInterface $billingInfoRepository
    ) {
        $this->sheetInfoGuesser      = $sheetInfoGuesser;
        $this->billingInfoRepository = $billingInfoRepository;
    }
    
    /**
     * @param TransactionViewQuery $query
     *
     * @return TransactionView
     */
    public function handle(TransactionViewQuery $query)
    {
        $paypalGateWay  = null;
        $paymentDetails = $query->payment->getDetails();
        
        if($query->transaction->getMode() === 'paypal' && isset($paymentDetails[self::PAYPAL_TRANSACTION_ID_KEY])) {
            $paypalGateWay = $paymentDetails[self::PAYPAL_TRANSACTION_ID_KEY];
        }
    
        $sheetInfos     = $this->sheetInfoGuesser->guessSheetInfos($query->sheet);
        $society        = $sheetInfos[Tag::SHEET_ORGANIZATION];
        $billingInfos   = $this->billingInfoRepository->getBySheet($query->sheet);
        $billingCountry = !$billingInfos ? null : $billingInfos->getAddress()->getCountry();
        $billingVat     = !$billingInfos ? null : $billingInfos->getVatNumber();
        
        return new TransactionView(
            $query->event,
            $query->sheet->getId(),
            $query->event->getId(),
            $query->event->getTitle(),
            $query->sheet->getOwner()->getId(),
            $society,
            $query->transaction->getDate(),
            $query->transaction->getMode(),
            $query->transaction->getReference(),
            $paypalGateWay,
            $query->transaction->getAmount(),
            $billingCountry,
            $billingVat
        );
    }
}
