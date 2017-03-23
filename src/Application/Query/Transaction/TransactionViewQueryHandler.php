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
use Proximum\Vimeet\Application\View\Transaction\TransactionView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Payment\PaymentRepositoryInterface;

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
     * @var PaymentRepositoryInterface
     */
    private $paymentRepository;
    
    /**
     * @var array
     */
    private $billingInfo = [];

    /**
     * @var array
     */
    private $payments = [];
    
    /**
     * TransactionViewQueryHandler constructor.
     *
     * @param SheetInfoGuesser                  $sheetInfoGuesser
     * @param BillingInfoRepositoryInterface    $billingInfoRepository
     * @param PaymentRepositoryInterface        $paymentRepository
     */
    public function __construct(
        SheetInfoGuesser $sheetInfoGuesser,
        BillingInfoRepositoryInterface $billingInfoRepository,
        PaymentRepositoryInterface $paymentRepository
    ) {
        $this->sheetInfoGuesser      = $sheetInfoGuesser;
        $this->billingInfoRepository = $billingInfoRepository;
        $this->paymentRepository     = $paymentRepository;
    }
    
    /**
     * @param Sheet[] $sheets
     */
    public function preloadBillingInfo(array $sheets)
    {
        $billingInfos = $this->billingInfoRepository->getBySheets($sheets);
        
        foreach ($billingInfos as $billingInfo) {
            $this->billingInfo[$billingInfo->getSheet()->getId()] = $billingInfo;
        }
    }

    /**
     * @param Transaction[] $transactions
     */
    public function preloadPayments(array $transactions)
    {
        $payments = $this->paymentRepository->getByTransactions($transactions);

        foreach ($payments as $payment) {
            $this->payments[$payment->getTransaction()->getId()] = $payment;
        }
    }
    
    /**
     * @param TransactionViewQuery $query
     *
     * @return TransactionView
     */
    public function handle(TransactionViewQuery $query)
    {
        $paypalGateWay  = null;

        if (!isset($this->payments[$query->transaction->getId()])) {
            $this->payments[$query->transaction->getId()] = $this->paymentRepository->getByTransaction($query->transaction);
        }

        if ($this->payments[$query->transaction->getId()] !== null) {
            $payment        = $this->payments[$query->transaction->getId()];
            $paymentDetails = $payment->getDetails();

            if ($query->transaction->getMode() === 'paypal' && isset($paymentDetails[self::PAYPAL_TRANSACTION_ID_KEY])) {
                $paypalGateWay = $paymentDetails[self::PAYPAL_TRANSACTION_ID_KEY];
            }
        }

        $sheetTitle = $this->sheetInfoGuesser->guessSheetTitle($query->sheet);
        
        if (!isset($this->billingInfo[$query->sheet->getId()])) {
            $this->billingInfo[$query->sheet->getId()] = $this->billingInfoRepository->getBySheet($query->sheet);
        }
        
        $billingInfos   = $this->billingInfo[$query->sheet->getId()];
        $billingCountry = !$billingInfos ? null : $billingInfos->getAddress()->getCountry();
        $billingVat     = !$billingInfos ? null : $billingInfos->getVatNumber();
        
        return new TransactionView(
            $query->event,
            $query->sheet->getId(),
            $query->event->getId(),
            $query->event->getTitle(),
            $query->sheet->getOwner()->getId(),
            $sheetTitle,
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
