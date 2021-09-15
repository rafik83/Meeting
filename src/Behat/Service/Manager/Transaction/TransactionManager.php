<?php

namespace Proximum\Vimeet\Behat\Service\Manager\Transaction;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Payment\Mode;
use Proximum\Vimeet\Domain\Repository\TransactionRepositoryInterface;

class TransactionManager
{
    /** @var TransactionRepositoryInterface */
    private $transactionRepository;

    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function createPaidTransaction(
        Sheet $sheet,
        float $amount,
        string $reference
    ): Transaction {
        return $this->createTransaction($sheet, $amount, $reference, Transaction::STATE_PAID);
    }

    public function createPendingTransaction(
        Sheet $sheet,
        float $amount,
        string $reference
    ): Transaction {
        return $this->createTransaction($sheet, $amount, $reference, Transaction::STATE_PENDING);
    }

    private function createTransaction(
        Sheet $sheet,
        float $amount,
        string $reference,
        string $status
    ): Transaction {
        $date = new \DateTime();

        $participants = $sheet->getParticipants();
        /** @var User */
        $user = null;
        if (count($participants)) {
            $user = $participants->first()->getUser();
        }

        $transaction = new Transaction(
            $sheet,
            $amount,
            $date,
            Mode::PAYMENT_BANK_CASH,
            $reference,
            $status,
            $sheet->getEvent()->getCurrency(),
            $user
        );

        $this->transactionRepository->add($transaction);

        return $transaction;
    }
}
