<?php

namespace Proximum\Vimeet\Infrastructure\Payum\Paypal;

use Payum\Core\Payum;
use Payum\Core\Request\GetHumanStatus;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Transaction\TransactionUpdatedEvent;
use Proximum\Vimeet\Domain\Model\Payment\Payment;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
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

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * @param Payum                          $payum
     * @param TransactionRepositoryInterface $transactionRepository
     * @param DelayedEventDispatcher         $eventDispatcher
     */
    public function __construct(
        Payum $payum,
        TransactionRepositoryInterface $transactionRepository,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->payum                 = $payum;
        $this->transactionRepository = $transactionRepository;
        $this->eventDispatcher       = $eventDispatcher;
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
                $transaction->unHide();
                $this->transactionRepository->set($transaction);

                $this->dispatchTransactionUpdatedEvent($transaction);

                return self::STATUS_SUCCESS;
            } elseif (Api::RECURRINGPAYMENTSTATUS_CANCELLED === $paymentStatus) {
                $transaction->setCancelled();
                $transaction->unHide();
                $this->transactionRepository->set($transaction);

                $this->dispatchTransactionUpdatedEvent($transaction);

                return self::STATUS_CANCELLED;
            } elseif (Api::PAYMENTSTATUS_PENDING === $paymentStatus) {
                $this->dispatchTransactionUpdatedEvent($transaction);

                return self::STATUS_PENDING;
            }
        }

        return self::STATUS_ERROR;
    }

    /**
     * @param Transaction $transaction
     */
    private function dispatchTransactionUpdatedEvent(Transaction $transaction)
    {
        $this->eventDispatcher->dispatch(
            Events::TRANSACTION_UPDATED,
            new TransactionUpdatedEvent($transaction)
        );
    }
}
