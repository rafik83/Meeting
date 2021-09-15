<?php

namespace Proximum\Vimeet\Infrastructure\Payum\CCIP;

use Payum\Core\Payum;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Transaction\TransactionUpdatedEvent;
use Proximum\Vimeet\Application\ThirdParty\CCIP\Exception\InvalidPaymentNumber;
use Proximum\Vimeet\Application\ThirdParty\CCIP\Exception\PaymentTokenUnavailable;
use Proximum\Vimeet\Domain\Model\Payment\Payment;
use Proximum\Vimeet\Domain\Model\Payment\PaymentToken;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class CapturePayment
{
    private Payum $payum;
    private TransactionRepositoryInterface $transactionRepository;
    private DelayedEventDispatcher $eventDispatcher;

    public function __construct(
        Payum $payum,
        TransactionRepositoryInterface $transactionRepository,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->payum                 = $payum;
        $this->transactionRepository = $transactionRepository;
        $this->eventDispatcher       = $eventDispatcher;
    }

    public function processValid(string $captureToken, string $paymentNumber)
    {
        $paymentToken = $this->getPaymentToken($captureToken);

        $payment = $this->getPayment($paymentToken, $paymentNumber);

        $this->payum->getHttpRequestVerifier()->invalidate($paymentToken);

        $transaction = $payment->getTransaction();
        $transaction->setPaid();
        $transaction->unHide();
        $this->transactionRepository->set($transaction);

        $this->dispatchTransactionUpdatedEvent($transaction);
    }

    public function processCancel(string $captureToken)
    {
        $paymentToken = $this->getPaymentToken($captureToken);
        $payment = $this->getPayment($paymentToken, null);

        $this->payum->getHttpRequestVerifier()->invalidate($paymentToken);

        $transaction = $payment->getTransaction();
        $transaction->setCancelled();
        $transaction->unHide();
        $this->transactionRepository->set($transaction);

        $this->dispatchTransactionUpdatedEvent($transaction);
    }

    /**
     * @throws PaymentTokenUnavailable
     */
    private function getPaymentToken(string $captureToken): PaymentToken
    {
        $paymentToken = $this->payum->getTokenStorage()->find($captureToken);

        if ($paymentToken === null) {
            throw new PaymentTokenUnavailable('Payment token not found');
        }

        return $paymentToken;
    }

    /**
     * @throws InvalidPaymentNumber
     */
    private function getPayment(PaymentToken $token, ?string $paymentNumber): Payment
    {
        $storage = $this->payum->getStorage(Payment::class);
        $payment = $storage->find($token->getDetails());

        if ($paymentNumber !== null && $payment->getNumber() !== $paymentNumber) {
            throw new InvalidPaymentNumber('Invalid payment number for payment #' . $payment->getId());
        }

        return $payment;
    }

    private function dispatchTransactionUpdatedEvent(Transaction $transaction)
    {
        $this->eventDispatcher->dispatch(
            Events::TRANSACTION_UPDATED,
            new TransactionUpdatedEvent($transaction)
        );
    }
}
