<?php

namespace Proximum\Vimeet\Behat\Service\Manager\Transaction;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
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

    public function createPendingTransaction(
        Sheet $sheet,
        float $amount,
        string $reference
    ): Transaction {
        $date = new \DateTime();

        $transaction = new Transaction(
            $sheet,
            $amount,
            $date,
            Mode::PAYMENT_BANK_CASH,
            $reference,
            Transaction::STATE_PENDING,
            $sheet->getEvent()->getCurrency()
        );

        $this->transactionRepository->add($transaction);

        return $transaction;
    }
}
