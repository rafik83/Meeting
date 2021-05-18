<?php

namespace Proximum\Vimeet\Infrastructure\Payum\CCIP;

use Payum\Core\Payum;
use Payum\Core\Request\GetHumanStatus;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Transaction\TransactionUpdatedEvent;
use Proximum\Vimeet\Application\ThirdParty\CCIP\Exception\InvalidPaymentNumber;
use Proximum\Vimeet\Application\ThirdParty\CCIP\Exception\PaymentTokenUnavailable;
use Proximum\Vimeet\Domain\Model\Payment\Payment;
use Proximum\Vimeet\Domain\Model\Payment\PaymentToken;
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
        // check if token and number are valid
        /** @var PaymentToken $paymentToken */
        $paymentToken = $this->payum->getTokenStorage()->find($captureToken);

        if ($paymentToken === null) {
            throw new PaymentTokenUnavailable('Payment token not found');
        }

        $storage = $this->payum->getStorage(Payment::class);
        /** @var Payment $payment */
        $payment = $storage->find($paymentToken->getDetails());

        if ($payment->getNumber() !== $paymentNumber) {
            throw new InvalidPaymentNumber('Invalid payment number for payment #' . $payment->getId());
        }

        $this->payum->getHttpRequestVerifier()->invalidate($paymentToken);

        $transaction = $payment->getTransaction();
        $transaction->setPaid();
        $transaction->unHide();
        $this->transactionRepository->set($transaction);

        $this->eventDispatcher->dispatch(
            Events::TRANSACTION_UPDATED,
            new TransactionUpdatedEvent($transaction)
        );

    }
}
