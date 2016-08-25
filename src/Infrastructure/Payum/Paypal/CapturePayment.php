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
use Payum\Core\Request\GetHumanStatus;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Proximum\Vimeet\Domain\Model\Payment\Payment;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;
use Proximum\Vimeet\Domain\Transaction\TransactionManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CapturePayment
{
    const STATUS_SUCCESS   = 'success';
    const STATUS_PENDING   = 'pending';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_ERROR     = 'error';

    /**
     * @var Payum
     */
    private $payum;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    private $transactionManager;

    /**
     * @param Payum                          $payum
     * @param TransactionRepositoryInterface $transactionRepository
     * @param TransactionManager             $transactionManager
     */
    public function __construct(
        Payum $payum,
        TransactionRepositoryInterface $transactionRepository,
        TransactionManager $transactionManager
    ) {
        $this->payum                 = $payum;
        $this->transactionRepository = $transactionRepository;
        $this->transactionManager    = $transactionManager;
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    public function process(Request $request)
    {
        try {
            $token = $this->payum->getHttpRequestVerifier()->verify($request);
        } catch (NotFoundHttpException $exception) {
            return self::STATUS_ERROR;
        }

        $gateway = $this->payum->getGateway($token->getGatewayName());
        $this->payum->getHttpRequestVerifier()->invalidate($token);
        $gateway->execute($status = new GetHumanStatus($token));

        /** @var Payment $payment */
        $payment = $status->getFirstModel();

        $details = $payment->getDetails();

        if (isset($details['PAYMENTINFO_0_PAYMENTSTATUS'])) {
            $paymentStatus = $details['PAYMENTINFO_0_PAYMENTSTATUS'];

            $transaction = $payment->getTransaction();

            if (Api::PAYMENTSTATUS_COMPLETED === $paymentStatus) {
                $transaction->setPaid();
                $this->transactionRepository->set($transaction);

                return self::STATUS_SUCCESS;
            } elseif (Api::RECURRINGPAYMENTSTATUS_CANCELLED === $paymentStatus) {
                $transaction->setCancelled();
                $this->transactionRepository->set($transaction);

                return self::STATUS_CANCELLED;
            } elseif (Api::PAYMENTSTATUS_PENDING === $paymentStatus) {
                return self::STATUS_PENDING;
            }
        }

        return self::STATUS_ERROR;
    }
}
