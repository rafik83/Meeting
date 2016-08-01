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
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Payment\Payment;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;

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
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var TranslatorAdapter
     */
    private $translator;

    /**
     * @param Payum                          $payum
     * @param BillingInfoRepositoryInterface $billingInfoRepository
     * @param SheetInfoGuesser               $sheetInfoGuesser
     * @param TranslatorAdapter              $translator
     */
    public function __construct(
        Payum $payum,
        BillingInfoRepositoryInterface $billingInfoRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        TranslatorAdapter $translator
    ) {
        $this->payum                 = $payum;
        $this->billingInfoRepository = $billingInfoRepository;
        $this->sheetInfoGuesser      = $sheetInfoGuesser;
        $this->translator            = $translator;
    }

    /**
     * @param Transaction $transaction
     * @param string      $locale
     *
     * @return TokenInterface
     */
    public function process(Transaction $transaction, $locale)
    {
        $billingInfo = $this->billingInfoRepository->getBySheet($transaction->getSheet());

        $storage = $this->payum->getStorage(Payment::class);

        $description = $this->translator->trans('order.transaction.paypal.description', [
            '%sheetName%' => $this->sheetInfoGuesser->guessSheetName(
                $transaction->getSheet(),
                $locale
            ),
            '%sheetId%'   => $transaction->getSheet()->getId(),
            '%eventName%' => $transaction->getSheet()->getEvent()->getTitle(),
        ]);

        $amount = $transaction->getAmount() * 100;

        /** @var Payment $payment */
        $payment = $storage->create();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode($transaction->getCurrency());
        $payment->setTotalAmount($amount);
        $payment->setClientId($transaction->getSheet()->getId());
        $payment->setClientEmail($billingInfo ? ($billingInfo->getEmail() ?: '') : '');
        $payment->setTransaction($transaction);
        $payment->setDescription($description);

        if (null !== $billingInfo) {
            $payment->setDetails(
                [
                    'FIRSTNAME'               => $billingInfo->getFirstname(),
                    'LASTNAME'                => $billingInfo->getLastname(),
                    'COUNTRYCODE'             => $billingInfo->getAddress()->getCountry(),
                    'SHIPTONAME'              => $billingInfo->getCompleteName(),
                    'SHIPTOSTREET'            => $billingInfo->getAddress()->getStreet(),
                    'SHIPTOCITY'              => $billingInfo->getAddress()->getCity(),
                    'SHIPTOSTATE'             => '',
                    'SHIPTOZIP'               => $billingInfo->getAddress()->getZipcode(),
                    'SHIPTOCOUNTRYCODE'       => $billingInfo->getAddress()->getCountry(),
                    'LOCALECODE'              => $locale,
                    'L_PAYMENTREQUEST_1_NAME' => $description,
                    'L_PAYMENTREQUEST_1_AMT'  => $amount,
                    'L_PAYMENTREQUEST_1_QTY'  => 1,
                    'PAYMENTREQUEST_1_AMT'    => $amount,
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
