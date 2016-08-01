<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Payum\Paypal;

use Payum\Core\Payum;
use Payum\Core\Security\TokenInterface;
use Proximum\Vimeet\Domain\Model\Payment\Payment;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;

class PreparePayment
{
    const GATEWAY_NAME = 'paypal_express_checkout';

    /**
     * @var Payum
     */
    private $payum;

    /**
     * @var BillingInfoRepositoryInterface
     */
    private $billingInfoRepository;

    /**
     * @param Payum                          $payum
     * @param BillingInfoRepositoryInterface $billingInfoRepository
     */
    public function __construct(Payum $payum, BillingInfoRepositoryInterface $billingInfoRepository)
    {
        $this->payum                 = $payum;
        $this->billingInfoRepository = $billingInfoRepository;
    }

    /**
     * @param Transaction $transaction
     *
     * @return TokenInterface
     */
    public function process(Transaction $transaction)
    {
        $billingInfo = $this->billingInfoRepository->getBySheet($transaction->getSheet());

        $storage = $this->payum->getStorage(Payment::class);

        /** @var Payment $payment */
        $payment = $storage->create();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode($transaction->getCurrency());
        $payment->setTotalAmount($transaction->getAmount() * 100);
        $payment->setClientId($transaction->getSheet()->getId());
        $payment->setClientEmail($billingInfo ? ($billingInfo->getEmail() ?: '') : '');
        $payment->setTransaction($transaction);

        if (null !== $billingInfo) {
            $payment->setDetails(
                [
                    'FIRSTNAME'         => $billingInfo->getFirstname(),
                    'LASTNAME'          => $billingInfo->getLastname(),
                    'COUNTRYCODE'       => $billingInfo->getAddress()->getCountry(),
                    'SHIPTONAME'        => $billingInfo->getCompleteName(),
                    'SHIPTOSTREET'      => $billingInfo->getAddress()->getStreet(),
                    'SHIPTOCITY'        => $billingInfo->getAddress()->getCity(),
                    'SHIPTOSTATE'       => '',
                    'SHIPTOZIP'         => $billingInfo->getAddress()->getZipcode(),
                    'SHIPTOCOUNTRYCODE' => $billingInfo->getAddress()->getCountry(),
                ]
            );
        }

        $storage->update($payment);

        return $this->payum->getTokenFactory()->createCaptureToken(
            self::GATEWAY_NAME,
            $payment,
            'event_package_payment_done', // the route to redirect after capture
            ['sheet' => $transaction->getSheet()->getId()]
        );
    }
}
